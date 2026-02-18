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
        })
            ->where('created_at', '>=', $user->created_at) // Only notifications after user registration
            ->latest()
            ->get();

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
        })
            ->where('created_at', '>=', $driver->created_at) // Only notifications after driver registration
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'notifications' => $notifications,
        ]);
    }

    public function sendToDriver(Request $request)
    {
        $this->validate($request, [
            'driver_id' => 'required|integer',
            'message' => 'required|string|max:500',
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $response = AdminFCMController::sendChatMessageToDriver(
                $request->message,
                $request->driver_id,
                $user->id  // ✅ استخدم user ID من المصادقة
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

    public function sendToUser(Request $request)
    {
        \Log::info('sendToUser hit', [
            'content_type' => $request->header('Content-Type'),
            'raw_body'     => $request->getContent(),
            'parsed'       => $request->all(),
            'driver'       => Auth::guard('driver-api')->id(),
        ]);
        // ✅ Handle driver app sending wrong Content-Type
        // Force merge JSON body if request->all() is empty
        if (empty($request->all()) && !empty($request->getContent())) {
            $jsonData = json_decode($request->getContent(), true);
            if (is_array($jsonData)) {
                $request->merge($jsonData);
            }
        }

        if ($request->has('sender_user_id') && !$request->has('user_id')) {
            $request->merge(['user_id' => $request->sender_user_id]);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $driver = Auth::guard('driver-api')->user();

            if (!$driver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Driver not authenticated'
                ], 401);
            }

            $response = AdminFCMController::sendChatMessageToUser(
                $request->message,
                $request->user_id,
                $driver->id
            );

            return response()->json([
                'status'  => (bool) $response,
                'message' => $response
                    ? 'Notification sent successfully to user'
                    : 'Failed to send notification to user'
            ], $response ? 200 : 400);
        } catch (\Exception $e) {
            \Log::error('sendToUser exception: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
