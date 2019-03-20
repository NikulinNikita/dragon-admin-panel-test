<?php

namespace App\Http\Controllers\api;

use DB;

use Admin\Http\Controllers\AmqpController;
use App\Http\Controllers\Controller;
use App\Models\StaffSession;
use \App\Models\LoopCommandEvent;
use \App\Models\Staff;
use Admin\Services\User\PersonalNotificationService;
use App\Notifications\User\PersonalNotification;
class ApiDealerController extends Controller
{
    public function roundStop()
    {
        $user = auth()->user();

        if (auth()->user()->isAbleTo(['manage_everything']) || auth()->user()->hasRole('dealer')) {
            $activeStaffSession = $user->staffSessions->where('status', 'active')->sortByDesc('id')->first();
            $table              = $activeStaffSession->table;

            AmqpController::stopLoop(null, $table);

            return response()->json(['message' => 'Round successfully stopped!'], 200);
        }

        return response()->json(['message' => 'Wrong user!'], 403);
    }

    public function getActiveSession(Int $id)
    {
        $activeSession = StaffSession::with('table.game', 'rouletteRounds', 'baccaratRounds')->where('table_id', $id)->where('status', 'active')->first();

        return response()->json(['activeSession' => $activeSession], 200);
    }

    public function dealerCommand()
    {
        if (!auth()->user()->hasRole('dealer')) {
            return response()->json(['message' => 'Wrong user!'], 403);
        }

        $code    = request()->get('key');
        $user    = auth()->user();

        $command = \App\Models\LoopCommand::where('code', '=', $code)->get()->first();

        if (!$command) {
            return response()->json(['message' => 'Undefined command'], 422);
        }

        try {
            DB::beginTransaction();

            $activeStaffSession = $user->staffSessions->where('status', 'active')->sortByDesc('id')->first();
            $table              = $activeStaffSession->table;
            $currentRound       = null;

            if ($table->game->slug == 'baccarat') {
               $currentRound = $this->getStartedRound($activeStaffSession->baccaratRounds()->get());
            } else if ($table->game->slug == 'roulette') {
                $currentRound = $this->getStartedRound($activeStaffSession->rouletteRounds()->get());
            } else {
                return response()->json(['message' => 'Undefined game'], 404);
            }

            if (!$currentRound) {
                return response()->json(['message' => 'Not found round'], 404);
            }

            $table   = $currentRound->staffSession->table;

            $loopCommandEvents = new LoopCommandEvent();

            $loopCommandEvents->loop_command_id  = $command->id;
            $loopCommandEvents->table_id         = $table->id;
            $loopCommandEvents->staff_session_id = $activeStaffSession->id;
            $loopCommandEvents->roundable_type   = $table->game->slug . '_round';
            $loopCommandEvents->roundable_id     = $currentRound->id;
            $loopCommandEvents->status           = 'not_processed';


            $loopCommandEvents->save();

            AmqpController::stopLoop(null, $table);

            $title = $command->description;
            $message = 'На столе под номером ' . $table->id;

            $this->notifyManagers($title, $message);

            DB::commit();

            return response()->json(['message' => 'Round successfully stopped!'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function getStartedRound($rounds)
    {
        foreach($rounds as $round) {
            if ($round->status == 'started') {
                return $round;
            }
        }

        return null;
    }

    private function notifyManagers($title, $message = '')
    {
        $managers = Staff::whereHas('roles', function ($query) use ($title) {
            $query->whereIn('name', ['manager']);
        })->get();

        $notify = new PersonalNotification(
            ['title' => $title, 'message' => $message, 'type' => 'danger', 'style' => 'danger', 'link' => '#']
        );

        if ($managers) {
            foreach ($managers as $manager) {
                (new PersonalNotificationService($manager, $notify))->send();
            }
        }
    }
}
