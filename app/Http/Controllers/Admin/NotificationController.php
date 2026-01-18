<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkNotification;
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
        $users = User::get();
        return view('admin.notifications.create', compact('users'));
    }

    public function send(Request $request)
    {
        // Validate the input
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'type' => 'required|in:0,1,2',
        ]);

        // Save the notification in the database first
        $noti = new Notification([
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type,
            'sent' => false, // Add this field to track status
        ]);

        $noti->save();

        // Dispatch the job to queue
        SendBulkNotification::dispatch(
            $request->title,
            $request->body,
            $request->type,
            $noti->id
        );

        return redirect()->back()->with('message', 'Notification queued and will be sent shortly');
    }
}
