<?php

namespace App\Services;

use App\Http\Controllers\Admin\FCMController as AdminFCMController;
use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;

class EnhancedFCMService extends AdminFCMController
{
    /**
     * Send new order notification to a specific driver
     */
    public static function sendNewOrderToDriver($driverId, $orderId, $distance, $userLocation = null)
    {
        $driver = Driver::find($driverId);
        $order = Order::with('user')->find($orderId);
        
        if (!$driver || !$order || !$driver->fcm_token) {
            \Log::error("Cannot send notification - Driver ID: $driverId, Order ID: $orderId");
            return false;
        }
        
        // Customize notification content
        $title = '🚗 طلب توصيل جديد';
        $body = "طلب جديد على بعد {$distance} كم - اضغط للقبول";
        
        // Add order details to notification data
        $orderData = [
            'order_id' => (string)$orderId,
            'driver_id' => (string)$driverId,
            'distance' => (string)$distance,
            'order_number' => $order->number ?? '',
            'user_name' => $order->user->name ?? 'مستخدم',
            'price' => (string)($order->total_price_after_discount ?? 0),
            'payment_method' => $order->payment_method->value ?? '',
            'screen' => 'new_order',
            'action' => 'accept_order',
            'status' => OrderStatus::Pending->value
        ];
        
        return self::sendMessageWithData(
            $title,
            $body,
            $driver->fcm_token,
            $driverId,
            $orderData
        );
    }
    
    /**
     * Send order status update notification to user
     */
    public static function sendOrderStatusToUser($orderId, OrderStatus $status)
    {
        $order = Order::with('user', 'driver')->find($orderId);
        
        if (!$order || !$order->user || !$order->user->fcm_token) {
            \Log::error("Cannot send status notification - Order ID: $orderId, Status: {$status->value}");
            return false;
        }
        
        $notificationData = self::getStatusNotificationData($status, $order);
        
        $orderData = [
            'order_id' => (string)$orderId,
            'status' => $status->value,
            'screen' => 'order_details',
            'action' => 'view_order',
            'driver_name' => $order->driver->name ?? '',
            'driver_phone' => $order->driver->phone ?? '',
            'order_number' => $order->number ?? ''
        ];
        
        return self::sendMessageWithData(
            $notificationData['title'],
            $notificationData['body'],
            $order->user->fcm_token,
            $order->user->id,
            $orderData
        );
    }
    
    /**
     * Send order status update notification to driver
     */
    public static function sendOrderStatusToDriver($orderId, OrderStatus $status, $customMessage = null)
    {
        $order = Order::with('user', 'driver')->find($orderId);
        
        if (!$order || !$order->driver || !$order->driver->fcm_token) {
            \Log::error("Cannot send status notification to driver - Order ID: $orderId, Status: {$status->value}");
            return false;
        }
        
        $notificationData = self::getDriverStatusNotificationData($status, $order, $customMessage);
        
        $orderData = [
            'order_id' => (string)$orderId,
            'status' => $status->value,
            'screen' => 'driver_order_details',
            'action' => 'view_order',
            'user_name' => $order->user->name ?? '',
            'order_number' => $order->number ?? ''
        ];
        
        return self::sendMessageWithData(
            $notificationData['title'],
            $notificationData['body'],
            $order->driver->fcm_token,
            $order->driver->id,
            $orderData
        );
    }
    
    /**
     * Get notification data based on order status for users
     */
    private static function getStatusNotificationData(OrderStatus $status, $order): array
    {
        $driverName = $order->driver->name ?? 'السائق';
        
        switch ($status) {
            case OrderStatus::DriverAccepted:
                return [
                    'title' => '✅ تم قبول طلبك',
                    'body' => "قام {$driverName} بقبول طلبك! سيصل إليك قريباً"
                ];
                
            case OrderStatus::DriverGoToUser:
                return [
                    'title' => '🚗 السائق في الطريق',
                    'body' => "{$driverName} في الطريق إليك الآن"
                ];
                
            case OrderStatus::Arrived:
                return [
                    'title' => '📍 وصل السائق',
                    'body' => "{$driverName} وصل إلى موقعك، يرجى الخروج"
                ];
                
            case OrderStatus::UserWithDriver:
                return [
                    'title' => '🎯 بدأت الرحلة',
                    'body' => "بدأت رحلتك مع {$driverName}"
                ];
                
            case OrderStatus::waitingPayment:
                return [
                    'title' => '💳 في انتظار الدفع',
                    'body' => 'وصلت إلى وجهتك، يرجى إتمام الدفع'
                ];
                
            case OrderStatus::Delivered:
                return [
                    'title' => '🎉 تم إنهاء الرحلة',
                    'body' => 'تم تسليم طلبك بنجاح! شكراً لاستخدام خدماتنا'
                ];
                
            case OrderStatus::DriverCancelOrder:
                return [
                    'title' => '❌ تم إلغاء الطلب',
                    'body' => "قام {$driverName} بإلغاء الطلب، نعتذر عن الإزعاج"
                ];
                
            case OrderStatus::UserCancelOrder:
                return [
                    'title' => '❌ تم إلغاء الطلب',
                    'body' => 'تم إلغاء طلبك بنجاح'
                ];
                
            default:
                return [
                    'title' => '📋 تحديث الطلب',
                    'body' => 'تم تحديث حالة طلبك'
                ];
        }
    }
    
    /**
     * Get notification data based on order status for drivers
     */
    private static function getDriverStatusNotificationData(OrderStatus $status, $order, $customMessage = null): array
    {
        $userName = $order->user->name ?? 'العميل';
        
        if ($customMessage) {
            return [
                'title' => '📋 تحديث الطلب',
                'body' => $customMessage
            ];
        }
        
        switch ($status) {
            case OrderStatus::UserCancelOrder:
                return [
                    'title' => '❌ تم إلغاء الطلب',
                    'body' => "قام {$userName} بإلغاء الطلب"
                ];
                
            case OrderStatus::Delivered:
                return [
                    'title' => '✅ تم إكمال الطلب',
                    'body' => 'تم تسليم الطلب بنجاح! تم إضافة المبلغ إلى رصيدك'
                ];
                
            default:
                return [
                    'title' => '📋 تحديث الطلب',
                    'body' => 'تم تحديث حالة الطلب'
                ];
        }
    }
    
   
     public static function sendMessageWithData($title, $body, $fcmToken, $userId, $customData = [])
    {
        if (!$fcmToken) {
            \Log::error("FCM Error: No FCM token provided for user ID $userId");
            return false;
        }
    
        $credentialsFilePath = base_path(env('FIREBASE_CREDENTIALS_PATH'));
    
        if (!file_exists($credentialsFilePath)) {
            \Log::error("FCM Error: Credentials file not found at: $credentialsFilePath");
            return false;
        }
    
        $jsonContent = file_get_contents($credentialsFilePath);
        $credentials = json_decode($jsonContent, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error("FCM Error: Invalid JSON in credentials file: " . json_last_error_msg());
            return false;
        }
    
        \Log::info("FCM Debug: Using project_id: " . ($credentials['project_id'] ?? 'NOT_FOUND'));
        \Log::info("FCM Debug: Client email: " . ($credentials['client_email'] ?? 'NOT_FOUND'));
    
        try {
            $client = new \Google_Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $tokenResponse = $client->getAccessToken();
    
            if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
                \Log::error("FCM Error: Failed to get access token");
                return false;
            }
    
            $access_token = $tokenResponse['access_token'];
            \Log::info("FCM Debug: Successfully got access token");
    
            $headers = [
                "Authorization: Bearer $access_token",
                'Content-Type: application/json'
            ];
    
            // Merge default custom data
            $dataPayload = array_merge([
                'screen' => 'order',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ], $customData);
    
            // Notification payload (no APNs section)
            $data = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $body
                    ],
                    "data" => $dataPayload,
                    "android" => [
                        "priority" => "high",
                        "notification" => [
                            "sound" => "default",
                            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                            "channel_id" => "order_notifications"
                        ]
                    ]
                ]
            ];
    
            $payload = json_encode($data);
            $projectId = env('FIREBASE_PROJECT_ID');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
    
            if ($result === false || $err) {
                \Log::error("FCM Error for user ID $userId: cURL Error: " . $err);
                return false;
            }
    
            $response = json_decode($result, true);
            \Log::info("FCM Response for user ID $userId: " . json_encode($response));
    
            if (isset($response['name'])) {
                return true;
            } else {
                \Log::error("FCM Error for user ID $userId: " . json_encode($response));
                if (isset($response['error']['details'][0]['errorCode']) && $response['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
                    \Log::info("FCM token cleanup for user ID $userId");
                    // Optionally clear token here
                }
                return false;
            }
        } catch (\Exception $e) {
            \Log::error("FCM Error for user ID $userId: " . $e->getMessage());
            \Log::error("FCM Error Stack Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    
    /**
     * Send bulk notifications to multiple drivers
     */
    public static function sendBulkToDrivers(array $driverIds, $title, $body, $customData = [])
    {
        $sent = 0;
        $failed = 0;
        
        $drivers = Driver::whereIn('id', $driverIds)
            ->whereNotNull('fcm_token')
            ->get();
            
        foreach ($drivers as $driver) {
            $result = self::sendMessageWithData($title, $body, $driver->fcm_token, $driver->id, $customData);
            
            if ($result) {
                $sent++;
            } else {
                $failed++;
            }
            
            // Small delay to prevent rate limiting
            usleep(50000); // 50ms
        }
        
        return [
            'sent' => $sent,
            'failed' => $failed,
            'total' => count($drivers)
        ];
    }
    
    /**
     * Send payment reminder notification
     */
    public static function sendPaymentReminder($orderId)
    {
        $order = Order::with('user')->find($orderId);
        
        if (!$order || !$order->user || !$order->user->fcm_token) {
            return false;
        }
        
        $title = '💳 تذكير بالدفع';
        $body = 'يرجى إتمام عملية الدفع لإنهاء الرحلة';
        
        $orderData = [
            'order_id' => (string)$orderId,
            'status' => OrderStatus::waitingPayment->value,
            'screen' => 'payment',
            'action' => 'pay_now'
        ];
        
        return self::sendMessageWithData($title, $body, $order->user->fcm_token, $order->user->id, $orderData);
    }
    
    /**
     * Send driver arrival notification
     */
    public static function sendDriverArrivalNotification($orderId)
    {
        $order = Order::with('user', 'driver')->find($orderId);
        
        if (!$order || !$order->user || !$order->user->fcm_token) {
            return false;
        }
        
        $driverName = $order->driver->name ?? 'السائق';
        $title = '📍 وصل السائق';
        $body = "{$driverName} وصل إلى موقعك، يرجى الخروج";
        
        $orderData = [
            'order_id' => (string)$orderId,
            'status' => OrderStatus::Arrived->value,
            'screen' => 'order_details',
            'action' => 'driver_arrived',
            'driver_name' => $driverName,
            'driver_phone' => $order->driver->phone ?? ''
        ];
        
        return self::sendMessageWithData($title, $body, $order->user->fcm_token, $order->user->id, $orderData);
    }

    /**
     * Send general notification (can be used for promotions, announcements, etc.)
     */
    public static function sendGeneralNotification($userId, $title, $body, $screen = 'home', $action = 'view')
    {
        // Determine if it's a user or driver
        $user = User::find($userId);
        $fcmToken = null;
        
        if ($user) {
            $fcmToken = $user->fcm_token;
        } else {
            $driver = Driver::find($userId);
            if ($driver) {
                $fcmToken = $driver->fcm_token;
            }
        }
        
        if (!$fcmToken) {
            \Log::error("FCM Error: No FCM token found for user ID $userId");
            return false;
        }
        
        $customData = [
            'screen' => $screen,
            'action' => $action,
            'type' => 'general'
        ];
        
        return self::sendMessageWithData($title, $body, $fcmToken, $userId, $customData);
    }
}