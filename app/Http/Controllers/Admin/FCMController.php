<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\AppSetting;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Google\Client as GoogleClient;

class FCMController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function sendMessage($title, $body, $fcmToken, $userId, $screen = "order", $userType = "user")
    {
        if (!$fcmToken) {
            \Log::error("FCM Error: No FCM token provided for $userType ID $userId");
            return false;
        }

        $credentialsFilePath = base_path(env('FIREBASE_CREDENTIALS_PATH'));

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();
            $client->fetchAccessTokenWithAssertion();
            $tokenResponse = $client->getAccessToken();

            $access_token = $tokenResponse['access_token'];
            \Log::info("FCM Access Token for $userType ID $userId: " . $access_token);

            $headers = [
                "Authorization: Bearer $access_token",
                'Content-Type: application/json'
            ];

            $data = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $body
                    ],
                    "data" => [
                        'screen' => $screen,
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                    ],
                    "android" => [
                        "priority" => "high"
                    ]
                ]
            ];

            $payload = json_encode($data);
            $projectId = env('FIREBASE_PROJECT_ID');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($result === false || $err) {
                \Log::error("FCM Error for $userType ID $userId: cURL Error: " . $err);
                return false;
            } else {
                $response = json_decode($result, true);
                \Log::info("FCM Response for $userType ID $userId: " . json_encode($response));
                
                if (isset($response['name'])) {
                    return true;
                } else {
                    \Log::error("FCM Error for $userType ID $userId: " . json_encode($response));
                    
                    // Clear invalid token based on user type
                    if (isset($response['error']['details'][0]['errorCode']) && 
                        $response['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
                        \Log::info("FCM token cleanup for $userType ID $userId");
                        
                        // if ($userType === 'driver') {
                        //     Driver::where('id', $userId)->update(['fcm_token' => null]);
                        // } else {
                        //     User::where('id', $userId)->update(['fcm_token' => null]);
                        // }
                    }
                    return false;
                }
            }
        } catch (\Exception $e) {
            \Log::error("FCM Error for $userType ID $userId: " . $e->getMessage());
            return false;
        }
    }

   public static function sendMessageToAll($title, $body, $type = 0): bool
    {
        $users = collect();

        if ($type == 0 || $type == 1) {
            // Fetch all users (or only users if type == 1)
            $userQuery = User::query()->whereNotNull('fcm_token');

            if ($type == 1) {
                // Add any specific filtering for "users" if needed
                // e.g., $userQuery->where('user_type', 1);
            }

            $users = $users->merge($userQuery->get());
        }

        if ($type == 0 || $type == 2) {
            // Fetch all drivers
            $driverQuery = Driver::query()->whereNotNull('fcm_token');
            $users = $users->merge($driverQuery->get());
        }

        if ($users->isEmpty()) {
            \Log::warning("No recipients found for FCM notification with type: $type");
            return false;
        }

        $allSent = true;

        foreach ($users as $recipient) {
            $fcmToken = $recipient->fcm_token ?? null;
            $userId = $recipient->id;

            $sent = self::sendMessage($title, $body, $fcmToken, $userId);

            if (!$sent) {
                $allSent = false;
                \Log::error("FCM notification failed for recipient ID $userId");
            }
        }

        return $allSent;
    }


    
    public static function sendChatMessageToDriver($message, $driver_id, $sender_user_id): bool
    {
        $driver = Driver::find($driver_id);
        if (!$driver || is_null($driver->fcm_token)) {
            \Log::error("Driver not found or has no FCM token for driver ID " . $driver_id);
            return false;
        }

        $user = User::find($sender_user_id);
        $userName = $user ? $user->name : 'User';

        // Pass 'driver' as the userType parameter
        $sent = self::sendMessage($userName, $message, $driver->fcm_token, $driver->id, "chat", "driver");

        if ($sent) {
            \Log::info("Chat notification sent to driver ID " . $driver_id . " from user ID " . $sender_user_id);
        } else {
            \Log::error("Failed to send notification to driver ID " . $driver_id);
        }

        return $sent;
    }

    public static function sendChatMessageToUser($message, $user_id, $sender_driver_id): bool
    {
        $user = User::find($user_id);
        if (!$user || is_null($user->fcm_token)) {
            \Log::error("User not found or has no FCM token for user ID " . $user_id);
            return false;
        }

        $driver = Driver::find($sender_driver_id);
        $driverName = $driver ? $driver->name : 'Driver';

        // Pass 'user' as the userType parameter (or omit it since 'user' is default)
        $sent = self::sendMessage($driverName, $message, $user->fcm_token, $user->id, "chat", "user");

        if ($sent) {
            \Log::info("Chat notification sent to user ID " . $user_id . " from driver ID " . $sender_driver_id);
        } else {
            \Log::error("Failed to send notification to user ID " . $user_id);
        }

        return $sent;
    }

}
