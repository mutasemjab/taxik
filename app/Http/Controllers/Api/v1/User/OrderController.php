<?php
namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Traits\Responses;
use App\Models\OrderSpam;
use App\Models\ServicePayment;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\DriverLocationService;
use App\Services\EnhancedFCMService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\FCMController as AdminFCMController;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use Illuminate\Validation\Rule;
use App\Services\OrderPaymentService;


class OrderController extends Controller
{
    use Responses;

  protected $driverLocationService;
    protected $orderPaymentService;
    
    public function __construct(
        DriverLocationService $driverLocationService,
        OrderPaymentService $orderPaymentService
    ) {
        $this->driverLocationService = $driverLocationService;
        $this->orderPaymentService = $orderPaymentService;
    }

    public function markAsDelivered(Request $request, $id)
    {
        $user = Auth::guard('user-api')->user();
        $order = Order::with(['service', 'user', 'driver'])->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$order) {
            return $this->error_response('Order not found', null);
        }
        
        // Check if order can be marked as delivered
        $allowedStatuses = [
            OrderStatus::waitingPayment,
            OrderStatus::Arrived
        ];
        
        if (!in_array($order->status, $allowedStatuses)) {
            return $this->error_response('Order cannot be marked as delivered at this stage', [
                'current_status' => $order->status->value,
                'allowed_statuses' => array_map(fn($status) => $status->value, $allowedStatuses)
            ]);
        }
        
        try {
            // Use the service to mark as delivered and process payment
            $result = $this->orderPaymentService->markAsDeliveredAndProcessPayment($order);
            
            if (!$result['success']) {
                return $this->error_response('Error processing order delivery', $result['error']);
            }
            
            $order = $result['order'];
            $paymentDetails = $result['payment_details'];
            
            // Send notification about order completion
            EnhancedFCMService::sendOrderStatusToUser($id, OrderStatus::Delivered);
            
            // You might want to notify the driver as well
            if ($order->driver) {
                EnhancedFCMService::sendOrderStatusToDriver($id, OrderStatus::Delivered, 'Order has been confirmed as delivered by the user');
            }
            
            $responseData = [
                'order_id' => $order->id,
                'status' => $order->status->value,
                'status_text' => $order->getStatusText(),
                'payment_status' => $order->status_payment->value,
                'payment_status_text' => $order->getPaymentStatusText(),
                'trip_started_at' => $order->trip_started_at,
                'trip_completed_at' => $order->trip_completed_at,
                'actual_duration_minutes' => $order->actual_trip_duration_minutes,
                'payment_details' => $paymentDetails
            ];
            
            return $this->success_response('Order marked as delivered successfully', $responseData);
            
        } catch (\Exception $e) {
            Log::error('Error marking order as delivered: ' . $e->getMessage());
            return $this->error_response('Error marking order as delivered', $e->getMessage());
        }
    }
   

   public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pick_name' => 'required',
            'drop_name' => 'nullable',
            'start_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'end_lat'   => 'nullable|numeric',
            'end_lng'   => 'nullable|numeric',
            'service_id' => 'required|exists:services,id',
            'total_price_before_discount' => 'nullable|numeric|min:0',
            'payment_method' => ['nullable', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
            'coupon_code' => 'nullable|string|exists:coupons,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $service = Service::where('id', $request->service_id)
                            ->where('activate', 1)
                            ->first();

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found or inactive'
                ], 404);
            }

            // Default to cash if not provided
            $paymentMethodValue = $request->payment_method ?? PaymentMethod::Cash->value;

            $isPaymentSupported = ServicePayment::where('service_id', $request->service_id)
                ->where('payment_method', $paymentMethodValue)
                ->exists();

            if (!$isPaymentSupported) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment method not supported for this service'
                ], 200);
            }

            $number = Order::generateOrderNumber();

            // Calculate price if not provided
            $calculatedPrice = $request->total_price_before_discount;
            if (!$calculatedPrice) {
                $distance = $this->calculateDistance(
                    $request->start_lat,
                    $request->start_lng,
                    $request->end_lat,
                    $request->end_lng
                );
                $calculatedPrice = $service->start_price + ($distance * $service->price_per_km);
            }

            // Initialize discount variables
            $discountValue = 0;
            $couponId = null;
            $finalPrice = $calculatedPrice;

            // Handle coupon validation and discount calculation
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)
                            ->where('activate', 1)
                            ->first();

                if (!$coupon) {
                    return response()->json([
                        'status' => false,
                        'type' => 'coupon_not_found',
                        'message' => 'Coupon not found or inactive'
                    ], 200);
                }

                // Check if coupon is valid (date range)
                if (!$coupon->isValid()) {
                    return response()->json([
                        'status' => false,
                        'type' => 'expired',
                        'message' => 'Coupon has expired or not yet active'
                    ], 200);
                }

                // Check if user has already used this coupon
                $hasUsedCoupon = DB::table('user_coupons')
                    ->where('user_id', auth()->id())
                    ->where('coupon_id', $coupon->id)
                    ->exists();

                if ($hasUsedCoupon) {
                    return response()->json([
                        'status' => false,
                        'type' => 'coupon_already_used',
                        'message' => 'You have already used this coupon'
                    ], 200);
                }

                // Check coupon usage limit (if number_of_used is not null)
                if (!is_null($coupon->number_of_used)) {
                    $currentUsageCount = DB::table('user_coupons')
                        ->where('coupon_id', $coupon->id)
                        ->count();

                    if ($currentUsageCount >= $coupon->number_of_used) {
                        return response()->json([
                            'status' => false,
                            'type' => 'coupon_usage_limit_reached',
                            'message' => 'This coupon has reached its maximum usage limit'
                        ], 200);
                    }
                }

                // Check minimum amount
                if ($calculatedPrice < $coupon->minimum_amount) {
                    return response()->json([
                        'status' => false,
                        'type' => 'minimum_amount',
                        'message' => 'Order amount does not meet minimum requirement for this coupon'
                    ], 200);
                }

                // Check coupon type restrictions
                if ($coupon->coupon_type == 2) { // First ride only
                    $previousOrdersCount = Order::where('user_id', auth()->id())
                                            ->whereIn('status', ['completed'])
                                            ->count();
                    
                    if ($previousOrdersCount > 0) {
                        return response()->json([
                            'status' => false,
                            'type' => 'coupon_first_ride_only',
                            'message' => 'This coupon is only valid for first ride'
                        ], 200);
                    }
                } elseif ($coupon->coupon_type == 3) { // Specific service only
                    if ($coupon->service_id != $request->service_id) {
                        return response()->json([
                            'status' => false,
                            'type' => 'coupon_invalid_service',
                            'message' => 'This coupon is not valid for the selected service'
                        ], 200);
                    }
                }

                // Calculate discount
                if ($coupon->discount_type == 1) { // Fixed amount
                    $discountValue = $coupon->discount;
                } else { // Percentage
                    $discountValue = ($calculatedPrice * $coupon->discount) / 100;
                }

                // Ensure discount doesn't exceed total price
                $discountValue = min($discountValue, $calculatedPrice);
                $finalPrice = $calculatedPrice - $discountValue;
                $couponId = $coupon->id;
            }

            // Use DB transaction to ensure data consistency
            DB::beginTransaction();

            try {
                $order = Order::create([
                    'number' => $number,
                    'status' => OrderStatus::Pending,
                    'payment_method' => PaymentMethod::from($paymentMethodValue),
                    'status_payment' => StatusPayment::Pending,
                    'total_price_before_discount' => $calculatedPrice,
                    'discount_value' => $discountValue,
                    'total_price_after_discount' => $finalPrice,
                    'net_price_for_driver' => $finalPrice,
                    'commision_of_admin' => 1,
                    'user_id' => auth()->id(),
                    'service_id' => $request->service_id,
                    'coupon_id' => $couponId,
                    'pick_lat' => $request->start_lat,
                    'pick_lng' => $request->start_lng,
                    'pick_name' => $request->pick_name,
                    'drop_name' => $request->drop_name,
                    'drop_lat' => $request->end_lat,
                    'drop_lng' => $request->end_lng,
                    'estimated_time' => $this->calculateEstimatedTime(
                        $request->start_lat,
                        $request->start_lng,
                        $request->end_lat,
                        $request->end_lng
                    ),
                ]);

                // Record coupon usage if a coupon was applied
                if ($couponId) {
                    DB::table('user_coupons')->insert([
                        'coupon_id' => $couponId,
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::commit();

                $result = $this->driverLocationService->findAndStoreOrderInFirebase(
                    $request->start_lat,
                    $request->start_lng,
                    $order->id,
                    $request->service_id,
                    $request->radius ?? 10000,
                    OrderStatus::Pending->value
                );

                return response()->json([
                    'status' => $result['success'],
                    'message' => $result['success'] 
                        ? 'Order created and drivers notified successfully' 
                        : $result['message'],
                    'data' => [
                        'order' => $order->load(['service', 'coupon']),
                        'service' => $service,
                        'coupon_applied' => $couponId ? true : false,
                        'discount_applied' => $discountValue,
                        'drivers_notified' => $result['drivers_found'] ?? [],
                        'notifications_sent' => $result['notifications_sent'] ?? [],
                        'notifications_failed' => $result['notifications_failed'] ?? [],
                        'user_location' => [
                            'start_lat' => $request->start_lat,
                            'start_lng' => $request->start_lng,
                            'end_lat' => $request->end_lat,
                            'end_lng' => $request->end_lng,
                        ]
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error creating order: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
    
    /**
     * Display a listing of the user's orders
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:1,2,3,4,5,6,7',
            'payment_status' => 'sometimes|in:1,2',
            'payment_method' => 'sometimes|in:1,2,3',
            'per_page' => 'sometimes|integer|min:5|max:100',
            'sort_by' => 'sometimes|in:date,price',
            'sort_direction' => 'sometimes|in:asc,desc',
            'from_date' => 'sometimes|date_format:Y-m-d',
            'to_date' => 'sometimes|date_format:Y-m-d|after_or_equal:from_date'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        $query = Order::where('user_id', $user->id);
        
      
        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        } elseif ($sortBy === 'price') {
            $sortBy = 'total_price_after_discount';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->with(['driver', 'service','coupon'])->paginate($perPage);
        
        // Transform data to include status text and other helper methods
       $orders->getCollection()->transform(function ($order) use ($user) {
        $order->status_text = $order->getStatusText();
        $order->payment_method_text = $order->getPaymentMethodText();
        $order->payment_status_text = $order->getPaymentStatusText();
        $order->distance = $order->getDistance();
    
        $hasRated = \App\Models\Rating::where('user_id', $user->id)
            ->where('order_id', $order->id)->where('driver_id', $order->driver_id)
            ->exists();
    
        $order->is_review = $hasRated ? 1 : 2;
    
        if (empty($order->estimated_time) && !is_null($order->drop_lat) && !is_null($order->drop_lng)) {
            $order->estimated_time = $this->calculateEstimatedTime(
                $order->pick_lat,
                $order->pick_lng,
                $order->drop_lat,
                $order->drop_lng
            );
        }
    
        return $order;
      });

        
        $responseData = [
            'orders' => $orders,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total()
            ]
        ];
        
        return $this->success_response('Orders retrieved successfully', $responseData);
    }
    
    
    /**
     * Display the specified order
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with([
                'driver','driver.ratings',
                'service','coupon'
            ])
            ->first();
        
        if (!$order) {
            return $this->error_response('Order not found', null);
        }
        
        // Add helper attributes
        $order->status_text = $order->getStatusText();
        $order->payment_method_text = $order->getPaymentMethodText();
        $order->payment_status_text = $order->getPaymentStatusText();
        $order->distance = $order->getDistance();
        $order->discount_percentage = $order->getDiscountPercentage();
        
        $hasRated = \App\Models\Rating::where('user_id', $user->id)
            ->where('order_id', $order->id)->where('driver_id', $order->driver_id)
            ->exists();
        
        $order->is_review = $hasRated ? 1 : 2;


         if (empty($order->estimated_time) && !is_null($order->drop_lat) && !is_null($order->drop_lng)) {
            $order->estimated_time = $this->calculateEstimatedTime(
                $order->pick_lat,
                $order->pick_lng,
                $order->drop_lat,
                $order->drop_lng
            );
            // Optionally save it to database
            
        }
        return $this->success_response('Order details retrieved successfully', $order);
    }
    
    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        
        $order = Order::with('service')->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$order) {
            return $this->error_response('Order not found', null);
        }
        
        $validator = Validator::make($request->all(), [
            'reason_for_cancel' => 'required|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        try {
            \DB::beginTransaction();
            
            $isPendingOrder = $order->status === OrderStatus::Pending;
            $isDriverAccepted = $order->status === OrderStatus::DriverAccepted;
            
            // Check if cancellation fee should be applied
            $cancellationFeeApplied = false;
            $cancellationFeeAmount = 0;
            
            if ($isDriverAccepted && $order->service) {
                $cancellationFeeAmount = $order->service->cancellation_fee;
                
                if ($cancellationFeeAmount > 0) {
                     $this->deductCancellationFee($user->id, $order->id, $cancellationFeeAmount);
                    $cancellationFeeApplied = true;
                }
            }
            
            if ($isPendingOrder) {
                // Move pending order to spam_orders table
                $spamOrder = $this->moveOrderToSpamTable($order, $request->reason_for_cancel);
                $order->delete();
                
                $responseData = [
                    'order_id' => $id,
                    'spam_order_id' => $spamOrder->id,
                    'status' => 'moved_to_spam',
                    'status_text' => 'Order cancelled and moved to spam',
                    'cancellation_reason' => $request->reason_for_cancel,
                    'message' => 'Pending order cancelled and moved to spam orders',
                    'cancellation_fee_applied' => false,
                    'cancellation_fee_amount' => 0
                ];
            } else {
                // For non-pending orders, just update status
                $order->status = OrderStatus::UserCancelOrder;
                $order->reason_for_cancel = $request->reason_for_cancel;
                $order->save();
                
                // Notify driver about cancellation
                if ($order->driver_id) {
                    EnhancedFCMService::sendOrderStatusToDriver($id, OrderStatus::UserCancelOrder);
                }
                
                $responseData = [
                    'order_id' => $order->id,
                    'status' => $order->status->value,
                    'status_text' => $order->getStatusText(),
                    'cancellation_reason' => $order->reason_for_cancel,
                    'message' => 'Order cancelled successfully',
                    'cancellation_fee_applied' => $cancellationFeeApplied,
                    'cancellation_fee_amount' => $cancellationFeeAmount,
                    'remaining_balance' => $user->fresh()->balance
                ];
            }
            
            \DB::commit();
            
            return $this->success_response('Order cancelled successfully', $responseData);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error cancelling order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deduct cancellation fee from user's balance
     */
    private function deductCancellationFee($userId, $orderId, $amount)
    {
        // Create wallet transaction record
        \DB::table('wallet_transactions')->insert([
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $amount,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Cancellation fee for order after driver acceptance",
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Update user's balance
        \DB::table('users')
            ->where('id', $userId)
            ->decrement('balance', $amount);
        
    }


    /**
     * Move order to spam_orders table
     */
    private function moveOrderToSpamTable($order, $reasonForCancel)
    {
        $spamOrder = OrderSpam::create([
            'number' => $order->number,
            'status' => OrderStatus::UserCancelOrder->value,
            'payment_method' => $order->payment_method->value,
            'status_payment' => $order->status_payment->value,
            'total_price_before_discount' => $order->total_price_before_discount,
            'total_price_after_discount' => $order->total_price_after_discount,
            'net_price_for_driver' => $order->net_price_for_driver,
            'commision_of_admin' => $order->commision_of_admin,
            'user_id' => $order->user_id,
            'service_id' => $order->service_id,
            'driver_id' => $order->driver_id,
            'pick_lat' => $order->pick_lat,
            'pick_lng' => $order->pick_lng,
            'pick_name' => $order->pick_name,
            'drop_name' => $order->drop_name,
            'drop_lat' => $order->drop_lat,
            'drop_lng' => $order->drop_lng,
            'estimated_time' => $order->estimated_time,
            'trip_started_at' => $order->trip_started_at,
            'trip_completed_at' => $order->trip_completed_at,
            'actual_trip_duration_minutes' => $order->actual_trip_duration_minutes,
            'reason_for_cancel' => $reasonForCancel,
            'cancelled_at' => now(),
        ]);
        
        return $spamOrder;
    }




    private function calculateEstimatedTime($lat1, $lng1, $lat2, $lng2)
    {
        // If destination coordinates are not provided, return null
        if (is_null($lat2) || is_null($lng2)) {
            return null;
        }
        
        // Calculate distance using existing method
        $distance = $this->calculateDistance($lat1, $lng1, $lat2, $lng2);
        
        // Average speed in km/h (you can adjust this based on your city/service type)
        $averageSpeed = 30; // 30 km/h for city driving
        
        // Calculate time in hours
        $timeInHours = $distance / $averageSpeed;
        
        // Convert to minutes
        $timeInMinutes = $timeInHours * 60;
        
        // Format the time string
        return $this->formatEstimatedTime($timeInMinutes);
    }

    
    
    private function formatEstimatedTime($minutes)
    {
        if ($minutes < 1) {
            return "Less than 1 minute";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = round($minutes % 60);
        
        if ($hours > 0) {
            if ($remainingMinutes > 0) {
                return "{$hours} hour" . ($hours > 1 ? 's' : '') . " {$remainingMinutes} minute" . ($remainingMinutes > 1 ? 's' : '');
            } else {
                return "{$hours} hour" . ($hours > 1 ? 's' : '');
            }
        } else {
            return "{$remainingMinutes} minute" . ($remainingMinutes > 1 ? 's' : '');
        }
    }

    /**
     * Calculate estimated time and update it in the order
     * @param Order $order
     * @return void
     */
    private function updateEstimatedTime($order)
    {
        $estimatedTime = $this->calculateEstimatedTime(
            $order->pick_lat,
            $order->pick_lng,
            $order->drop_lat,
            $order->drop_lng
        );
        
        if ($estimatedTime) {
            $order->estimated_time = $estimatedTime;
            $order->save();
        }
    }

}