<?php

namespace Admin\Http\Sections;

use AdminForm;
use App\Events\McMessage\MessageChange;
use App\Events\User\UserChatBlock;
use App\Models\BaseModel;
use App\Models\McConversation;
use App\Models\McMessage;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Chat;
use DB;
use Illuminate\Http\Response;

class McChats extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public static function sendMessage($id = null)
    {
        $user = User::where('name', 'admin')->first();
        if ( ! $user) {
            return response()->json(['error' => "No such user with name:'admin'"], 500);
        }

        $body         = request()->get('newMessage');
        $conversation = Chat::conversations()->getById($id);
        $message      = Chat::message($body)->from($user->id)->to($conversation)->send();

        return response()->json(['success' => 'success']);
    }

    public static function blockUser($id = null)
    {
        $id            = $id ?? request()->get('user_id');
        $blockDuration = Setting::where('key', 'user_chat_block_minutes')->first();
        $blockedUntil  = Carbon::now()->addMinutes($blockDuration->value ?? 30);
        $user          = User::whereId($id)->first();
        if ( ! $user) {
            return response()->json(['error' => "No such user with id:'{$id}'"], 500);
        }

        DB::transaction(function () use ($user, $blockedUntil) {
            $user->update(['blocked_chat_until' => $blockedUntil]);
            $userChatBlockEvent = new UserChatBlock($user);
            broadcast($userChatBlockEvent);
        });

        return response()->json(['success' => 'success', 'blocked_chat_until' => $blockedUntil->format(config('selectOptions.common.dateTime'))]);
    }

    public static function deleteMessage($id = null)
    {
        $id      = $id ?? request()->get('message_id');
        $message = McMessage::whereId($id)->first();
        if ( ! $message) {
            return response()->json(['error' => "No such message with id:'{$id}'"], 500);
        }

        DB::transaction(function () use ($message) {
            $message->update(['status' => 'inactive']);
            $messageDeleteEvent = new MessageChange($message);
            broadcast($messageDeleteEvent);
        });

        return response()->json(['success' => 'success']);
    }

    public static function getSectionData($id = null)
    {
        $conversations = McConversation::has('messages')->orderBy('id', 'desc');
        if ($title = request()->get('title')) {
            $conversations->where('id', 'like', '%' . $title . '%');
        }
        $conversations = $conversations->paginate(5, ['*'], 'page', request()->get('page') ?? 1);

        if (count($conversations)) {
            $messagesLimit        = Setting::where('key', 'messages_show_limit')->first();
            $selectedConversation = $conversations->where('id', $id ?? (request()->get('conversation_id') ?? $conversations->first()->id))->first();
            $selectedConversation = $selectedConversation ?? $conversations->sortByDesc('id')->first();
            $selectedConversation = $selectedConversation->load([
                'messages' => function ($q1) use ($messagesLimit) {
                    $q1->with([
                        'user' => function ($q2) {
                            $q2->select(['id', 'name', 'blocked_chat_until']);
                        }
                    ])->orderBy('id', 'desc')->limit($messagesLimit->value ?? 100);
                }
            ]);
            $selectedConversation->setRelation('messages', $selectedConversation->messages->sortBy('id')->values());
        } else {
            $selectedConversation = collect();
        }

        $response = [
                        'data' => compact('conversations', 'selectedConversation')
                    ] + BaseModel::addPaginationStatsOf(compact('conversations'));

        if (request()->is('*/vue/*')) {
            return response()->json($response);
        }

        return $response;
    }

    public function onDisplay($scopes = [])
    {
        return AdminForm::panel()->setView(view('admin::pages.mc_chats', self::getSectionData()));
    }
}
