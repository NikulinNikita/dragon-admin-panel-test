<?php

namespace Admin\Http\Controllers\User;


use Admin\Http\Controllers\Controller;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['notifications' => auth()->user()->unreadNotifications()->get()->pluck('data')->groupBy('style')]);
    }

    public function readAll()
    {
        if (auth()->user()->notifications()->where('read_at', null)->count()) {
            auth()->user()->notifications()->where('read_at', null)->update(['read_at' => Carbon::now()]);
        }

        return redirect()->back();
    }

    public function read(Staff $staff, string $notification_id = null)
    {

        if ($notification_id === null) {
            $staff->unreadNotifications()->update(['read_at' => now()]);
        } else {
            $staff->unreadNotifications()->where('id', $notification_id)->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function unread(Staff $staff, string $notification_id = null)
    {

        if ($notification_id === null) {
            $staff->unreadNotifications()->update(['read_at' => null]);
        } else {
            $staff->unreadNotifications()->where('id', $notification_id)->update(['read_at' => null]);
        }

        return response()->json(['success' => true]);
    }
}