<?php
namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Driver;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Services\EnhancedFCMService;
use App\Services\OrderPaymentService;
use Illuminate\Support\Facades\Log;

class OrderDriverController extends Controller
{
    use Responses;

    protected $orderPaymentService;
    
    public function __construct(OrderPaymentService $orderPaymentService)
    {
        $this->orderPaymentService = $orderPaymentService;
    }

    public function index(Request $request)
    {
        $driver = Auth::guard('driver-api')->user();
        
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
        
        $query = Order::where('driver_id', $driver->id);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status if provided
        if ($request->has('payment_status')) {
            $query->where('status_payment', $request->payment_status);
        }
        
        // Filter by payment method if provided
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        
        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        } elseif ($sortBy === 'price') {
            $sortBy = 'net_price_for_driver';
        }
        
        $query->orderBy($sortBy, $sortDirection);
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->with(['user', 'service','driver','coupon'])->paginate($perPage);
        
        // Transform data to include status text and other helper methods
        $orders->getCollection()->transform(function ($order) {
            $order->status_text = $order->getStatusText();
            $order->payment_method_text = $order->getPaymentMethodText();
            $order->payment_status_text = $order->getPaymentStatusText();
            $order->distance = $order->getDistance();
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
    
  
    public function show($id)
    {
        $driver = Auth::guard('driver-api')->user();
        
        $order = Order::where('id', $id)
            ->where('driver_id', $driver->id)
            ->with([
                'user:id,name,phone,country_code,photo,fcm_token','driver.ratings','driver',
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
        
        return $this->success_response('Order details retrieved successfully', $order);
    }
    
   
    public function cancelOrder(Request $request, $id)
    {
        $driver = Auth::guard('driver-api')->user();
        
        $order = Order::where('id', $id)
            ->where('driver_id', $driver->id)
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
        
        // Process cancellation
        $order->status = OrderStatus::DriverCancelOrder; // Driver cancelled order
        $order->reason_for_cancel = $request->reason_for_cancel;
        $order->save();
        
        // Check for penalty application
        $penaltyApplied = $this->checkAndApplyPenalty($driver->id, $order->id);
        
        // Notify the user about cancellation
        EnhancedFCMService::sendOrderStatusToUser($id, OrderStatus::DriverCancelOrder);
        
        $responseData = [
            'order_id' => $order->id,
            'status' => $order->status,
            'status_text' => $order->getStatusText(),
            'cancellation_reason' => $order->reason_for_cancel,
            'penalty_applied' => $penaltyApplied['applied'],
            'penalty_amount' => $penaltyApplied['amount'] ?? null,
            'cancellation_count_today' => $penaltyApplied['cancellation_count'],
            'allowed_cancellations' => $penaltyApplied['allowed_cancellations']
        ];
        
        return $this->success_response('Order cancelled successfully', $responseData);
    }

    /**
     * Check if driver exceeded cancellation limit and apply penalty
     */
    private function checkAndApplyPenalty($driverId, $orderId)
    {
        // Get settings
        $maxCancellations = $this->getSettingValue('times_that_driver_cancel_orders_in_one_day', 2);
        $penaltyFee = $this->getSettingValue('fee_when_driver_cancel_order_more_times', 0.5);
        
        // Count today's cancellations for this driver (including current one)
        $todayCancellations = Order::where('driver_id', $driverId)
            ->where('status', OrderStatus::DriverCancelOrder)
            ->whereDate('updated_at', today())
            ->count();
        
        $result = [
            'applied' => false,
            'cancellation_count' => $todayCancellations,
            'allowed_cancellations' => $maxCancellations
        ];
        
        // Apply penalty if exceeded limit
        if ($todayCancellations > $maxCancellations) {
            try {
                DB::beginTransaction();
                
                // Deduct from driver's wallet
                $this->deductFromDriverWallet($driverId, $orderId, $penaltyFee);
                
                DB::commit();
                
                $result['applied'] = true;
                $result['amount'] = $penaltyFee;
                
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Error applying cancellation penalty: ' . $e->getMessage());
                // Continue without penalty if there's an error
            }
        }
        
        return $result;
    }

    /**
     * Deduct penalty fee from driver's wallet
     */
    private function deductFromDriverWallet($driverId, $orderId, $amount)
    {
        // Create wallet transaction record
        \DB::table('wallet_transactions')->insert([
            'order_id' => $orderId,
            'driver_id' => $driverId,
            'amount' => $amount,
            'type_of_transaction' => 2, // withdrawal
            'note' => "Penalty for exceeding daily order cancellation limit",
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Update driver's wallet balance (assuming drivers table has a wallet_balance column)
        // If you don't have this column, you might need to calculate balance from transactions
        \DB::table('drivers')
            ->where('id', $driverId)
            ->decrement('balance', $amount);
        
        \Log::info("Penalty applied to driver {$driverId}: ${$amount} for order {$orderId}");
    }

    /**
     * Get setting value by key with default fallback
     */
    private function getSettingValue($key, $default = null)
    {
        $setting = DB::table('settings')
            ->where('key', $key)
            ->first();
            
        return $setting ? $setting->value : $default;
    }
    
   

    public function updateStatus(Request $request, $id)
    {
        $driver = Auth::guard('driver-api')->user();
        $order = Order::with(['service', 'user', 'driver'])->where('id', $id)
            ->where('driver_id', $driver->id)
            ->first();
            
        if (!$order) {
            return $this->error_response('Order not found', null);
        }
        
        // Define allowed status values using enum values
        $allowedStatuses = [
            OrderStatus::DriverGoToUser->value,
            OrderStatus::UserWithDriver->value, 
            OrderStatus::waitingPayment->value, 
            OrderStatus::Arrived->value,
            OrderStatus::Delivered->value,
        ];
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $allowedStatuses),
            'drop_name' => 'required_if:status,' . OrderStatus::waitingPayment->value,
            'drop_lat' => 'required_if:status,' . OrderStatus::waitingPayment->value,
            'drop_lng' => 'required_if:status,' . OrderStatus::waitingPayment->value,
        ]);
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        try {
            DB::beginTransaction();
            
            $currentStatus = $order->status;
            $newStatus = OrderStatus::from($request->status);
            
            // Track when trip starts
            if ($newStatus === OrderStatus::UserWithDriver && is_null($order->trip_started_at)) {
                $order->trip_started_at = now();
            }

            // Calculate pricing for waiting payment
            $pricingDetails = null;
            if ($newStatus === OrderStatus::waitingPayment) {
                $order->drop_name = $request->input('drop_name');
                $order->drop_lat = (float) $request->input('drop_lat');
                $order->drop_lng = (float) $request->input('drop_lng');
                $pricingDetails = $this->orderPaymentService->calculateFinalPrice($order);
                
                if ($pricingDetails['price_updated']) {
                    // Update the order with new pricing including recalculated discount
                    $order->total_price_before_discount = $pricingDetails['new_calculated_price'];
                    $order->discount_value = $pricingDetails['final_discount_value'];
                    $order->total_price_after_discount = $pricingDetails['final_price'];
                    $order->net_price_for_driver = $pricingDetails['net_price_for_driver'];
                    $order->commision_of_admin = $pricingDetails['admin_commission'];
                    
                    // Log coupon recalculation if it happened
                    if ($pricingDetails['coupon_recalculated']) {
                        Log::info("Order {$order->id}: Coupon discount updated from {$pricingDetails['initial_discount']} to {$pricingDetails['final_discount_value']}");
                    }
                }
                
                if ($order->trip_started_at && !$order->trip_completed_at) {
                    $tripCompletedAt = now();
                    $order->trip_completed_at = $tripCompletedAt;
                    $order->actual_trip_duration_minutes = $order->trip_started_at->diffInMinutes($tripCompletedAt);
                }
                
                // Update status and save
                $order->status = $newStatus;
                $order->save();
            }
            
            // Process payment when status is delivered using the service
            $paymentDetails = null;
            if ($newStatus === OrderStatus::Delivered) {
                $result = $this->orderPaymentService->markAsDeliveredAndProcessPayment($order, $driver);
                
                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }
                
                $order = $result['order']; // Get updated order from service
                $paymentDetails = $result['payment_details'];
            } else if ($newStatus !== OrderStatus::waitingPayment) {
                // Update status for other cases
                $order->status = $newStatus;
                $order->save();
            }
            
            DB::commit();
            
            // Send notification to user about status change
            EnhancedFCMService::sendOrderStatusToUser($id, $newStatus);
            
            // Special case for arrival notification
            if ($newStatus === OrderStatus::Arrived) {
                EnhancedFCMService::sendDriverArrivalNotification($id);
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
            ];
            
            if ($pricingDetails) {
                $responseData['pricing_details'] = $pricingDetails;
            }
            
            if ($paymentDetails) {
                $responseData['payment_details'] = $paymentDetails;
            }
            
            return $this->success_response('Order status updated successfully', $responseData);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order status: ' . $e->getMessage());
            
            return $this->error_response('Error updating order status', $e->getMessage());
        }
    }

    
    
    public function acceptOrder(Request $request, $orderId)
    {
            $driver = auth()->user(); // Assuming driver is authenticated
            
            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not authenticated'
                ], 401);
            }
            
            try {
                // Find the order
                $order = Order::with(['user', 'service'])->find($orderId);
                
                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
                
                // Check if order is still pending
                if ($order->status !== OrderStatus::Pending) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order is no longer available for acceptance',
                        'current_status' => $order->getStatusText()
                    ], 400);
                }
                
                
                // Begin database transaction
                DB::beginTransaction();
                
                try {
                    // Update order with driver information
                    $order->update([
                        'driver_id' => $driver->id,
                        'status' => OrderStatus::DriverAccepted,
                        'updated_at' => now()
                    ]);
                    
                    
                    DB::commit();
                    
                    EnhancedFCMService::sendOrderStatusToUser($orderId, OrderStatus::DriverAccepted);

                    
                    $responseData = [
                        'order' => $order->load('service','driver','user'),
                    ];
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Order accepted successfully',
                        'data' => $responseData
                    ], 200);
                    
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
                
            } catch (\Exception $e) {
                \Log::error('Error accepting order: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error accepting order: ' . $e->getMessage()
                ], 500);
            }
        }

}