<?php

namespace App\Helpers;

use Google\Client as GoogleClient;


class AppSetting
{

  public static function push_notification($token, $title, $body, $type, $order_id, $screen = "order")
{
    try {
        // Log the start of the notification process
        \Log::info('Starting push notification process.');

        $fcmToken = $token;
        $title = $title ?: "Noor Ordon";
        $description = $body;

        \Log::info("Notification Data", [
            'token' => $fcmToken,
            'title' => $title,
            'body' => $description,
            'type' => $type,
            'order_id' => $order_id,
            'screen' => $screen
        ]);

        // Replace 'path_to_credentials_file' with the actual path to your service account JSON file in the root directory
        $credentialsFilePath = base_path('json/noor-9754e-4241cd69821b.json');

        // Initialize the Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $client->fetchAccessTokenWithAssertion();
        $tokenResponse = $client->getAccessToken();

        if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
            \Log::error('Failed to fetch access token', ['tokenResponse' => $tokenResponse]);
            return response()->json([
                'message' => 'Failed to fetch access token'
            ], 500);
        }

        $access_token = $tokenResponse['access_token'];
        \Log::info('Access token retrieved successfully.');

        // Set the headers
        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        // Set the notification data
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
        \Log::info('Payload prepared', ['payload' => $payload]);

        // Initialize cURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/noor-9754e/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // This ensures cURL returns the response
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging

        $response = curl_exec($ch); // Execute the cURL request and get the response
        $err = curl_error($ch);
        curl_close($ch);

        // Log the raw response for debugging
        \Log::info('Raw FCM Response', ['response' => $response]);

        if ($err) {
            \Log::error('Curl Error', ['error' => $err]);
            return response()->json([
                'message' => 'Curl Error: ' . $err
            ], 500);
        }

        // Log the raw response before decoding
        $responseDecoded = json_decode($response, true);
        \Log::info('Notification response decoded', ['response' => $responseDecoded]);

        // Check FCM response for errors
        if (isset($responseDecoded['error'])) {
            \Log::error('FCM Error', ['response' => $responseDecoded]);
            return response()->json([
                'message' => 'FCM Error',
                'error' => $responseDecoded['error']
            ], 500);
        }

        // Log success
        \Log::info('Notification sent successfully.');
        return response()->json([
            'message' => 'Notification has been sent',
            'response' => $responseDecoded
        ]);
    } catch (\Exception $e) {
        \Log::error('Exception in sending notification', ['exception' => $e->getMessage()]);
        return response()->json([
            'message' => 'Error occurred while sending notification',
            'error' => $e->getMessage()
        ], 500);
    }
}



}

