<?php

namespace Admin\Http\Controllers;

use Illuminate\Http\Request;
use MessagesStack;

class RoundsController extends Controller
{
    public function stop(Request $request, $type, $id)
    {
        $typeBig = ucwords($type);
        $round   = app()->make("App\\Models\\{$typeBig}Round")->findOrFail($id);
        $table   = $round->staffSession->table;

        AmqpController::stopLoop(null, $table);
        MessagesStack::addSuccess("{$typeBig} Round with id '{$id}' has successfully been stopped!");

        return redirect()->back();
    }

    public function restart(Request $request, $type, $id)
    {
        $typeBig = ucwords($type);
        $round   = app()->make("App\\Models\\{$typeBig}Round")->findOrFail($id);
        $table   = $round->staffSession->table;

        if (count($round->loopCommandEvents)) {
            $loopCommandEvent = $round->loopCommandEvents->first();
            $loopCommandEvent->update(['status' => 'processed', 'staff_id' => auth()->id()]);
        }
        AmqpController::restartLoop(null, $table);

        MessagesStack::addSuccess("Loop has successfully been restarted!");

        return redirect()->back();
    }

    public function restartWithNoBets(Request $request, $type, $id)
    {
        $typeBig = ucwords($type);
        $round   = app()->make("App\\Models\\{$typeBig}Round")->findOrFail($id);
        $table   = $round->staffSession->table;

        if (count($round->loopCommandEvents)) {
            $loopCommandEvent = $round->loopCommandEvents->first();
            $loopCommandEvent->update(['status' => 'processed', 'staff_id' => auth()->id()]);
        }
        AmqpController::restartLoopWithNoBets(null, $table);

        MessagesStack::addSuccess("Loop has successfully been restarted with 'no-bets' next round!");

        return redirect()->back();
    }

    public function manipulateRound(Request $request, $type, $id)
    {
        $typeBig   = ucwords($type);
        $round     = app()->make("App\\Models\\{$typeBig}Round")->findOrFail($id);
        $table     = $round->staffSession->table;
        $roundData = ['round_id' => $round->id, 'revert' => true, 'mark' => true];

        AmqpController::recountRound($roundData, $type);
        AmqpController::restartLoop(null, $table);

        MessagesStack::addSuccess("{$typeBig} Round with id '{$id}' including all bets has successfully been recounted!");

        return redirect()->back();
    }

    public function refundBets(Request $request, $type, $id)
    {
        $typeBig   = ucwords($type);
        $roundData = ['round_id' => $id, 'purge' => true, 'mark' => true];

        AmqpController::recountRound($roundData, $type);

        MessagesStack::addSuccess("All bets of {$typeBig} Round with id '{$id}' has successfully been refunded!");

        return redirect()->back();
    }
}