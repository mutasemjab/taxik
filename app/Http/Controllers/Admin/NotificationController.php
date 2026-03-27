<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkNotification;
use App\Models\Driver;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:notification-add')->only('create', 'send');
    }

    public function create()
    {
        $users = User::select('id', 'name', 'phone')->get();
        $drivers = Driver::select('id', 'name', 'phone')->get();
        return view('admin.notifications.create', compact('users', 'drivers'));
    }

    public function send(Request $request)
    {
        $this->validate($request, [
            'title'     => 'required|string',
            'body'      => 'required|string',
            'type'      => 'required|in:0,1,2,3,4',
            // type 3 = specific user, type 4 = specific driver
            'target_id' => 'required_if:type,3,4|nullable|integer',
        ]);

        $noti = Notification::create([
            'title' => $request->title,
            'body'  => $request->body,
            'type'  => $request->type,
            'sent'  => false,
        ]);

        // For specific user / driver, send directly without queuing
        if ($request->type == 3) {
            $user = User::find($request->target_id);
            if ($user && $user->fcm_token) {
                FCMController::sendMessage($request->title, $request->body, $user->fcm_token, $user->id);
                $noti->update(['sent' => true]);
                Log::info("Targeted notification sent to user #{$user->id}");
            } else {
                Log::warning("Could not send targeted notification: user #{$request->target_id} not found or has no FCM token");
            }
            return redirect()->back()->with('message', __('messages.Notification_Sent_Successfully'));
        }

        if ($request->type == 4) {
            $driver = Driver::find($request->target_id);
            if ($driver && $driver->fcm_token) {
                FCMController::sendMessage($request->title, $request->body, $driver->fcm_token, $driver->id, null, 'driver');
                $noti->update(['sent' => true]);
                Log::info("Targeted notification sent to driver #{$driver->id}");
            } else {
                Log::warning("Could not send targeted notification: driver #{$request->target_id} not found or has no FCM token");
            }
            return redirect()->back()->with('message', __('messages.Notification_Sent_Successfully'));
        }

        // Bulk notifications (type 0, 1, 2) — dispatch to queue
        SendBulkNotification::dispatch(
            $request->title,
            $request->body,
            $request->type,
            $noti->id
        );

        return redirect()->back()->with('message', __('messages.Notification_Queued'));
    }
}
