<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Service;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Admin\FCMController as AdminFCMController;

class NotificationApiController extends Controller
{
    use Responses;


    // Fetch notifications for a user
    public function getUserNotifications(Request $request)
    {
        $user = $request->user(); // assuming sanctum or passport is used for auth
        $notifications = Notification::where(function ($query) use ($user) {
            $query->where('type', 0) // all
                ->orWhere('type', 1) // user
                ->orWhere(function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })->latest()->get();
        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }
    // Fetch notifications for a driver
    public function getDriverNotifications(Request $request)
    {
        $driver = $request->user(); // assuming driver is authenticated
        $notifications = Notification::where(function ($query) use ($driver) {
            $query->where('type', 0) // all
                ->orWhere('type', 2) // driver
                ->orWhere(function ($q) use ($driver) {
                    $q->where('driver_id', $driver->id);
                });
        })->latest()->get();
        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    public function sendToUser(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'message' => 'required|string|max:500',
        ]);

        try {
            $driver = $request->user(); // ✅ Get authenticated driver

            $response = AdminFCMController::sendChatMessageToUser(
                $request->message,
                $request->user_id,
                $driver->id  // ✅ Use authenticated driver's ID
            );
            if ($response) {
                return response()->json([
                    'status' => true,
                    'message' => 'Notification sent successfully to user'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send notification to user'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendToDriver(Request $request)
    {
        $this->validate($request, [
            'driver_id' => 'required|integer',
            'message' => 'required|string|max:500',
            'sender_user_id' => 'required|integer'
        ]);

        try {
            $response = AdminFCMController::sendChatMessageToDriver(
                $request->message,
                $request->driver_id,
                $request->sender_user_id
            );

            if ($response) {
                return response()->json([
                    'status' => true,
                    'message' => 'Notification sent successfully to driver'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send notification to driver'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
