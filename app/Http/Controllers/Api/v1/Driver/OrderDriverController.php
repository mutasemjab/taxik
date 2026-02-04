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
use App\Models\OrderRejection;
use App\Models\OrderStatusHistory;
use App\Models\WalletTransaction;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Services\EnhancedFCMService;
use App\Services\OrderPaymentService;
use App\Services\OrderStatusService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderDriverController extends Controller
{
    use Responses;

    protected $orderPaymentService;

    public function __construct(OrderPaymentService $orderPaymentService)
    {
        $this->orderPaymentService = $orderPaymentService;
    }

    public function rejectOrder($orderId)
    {
        try {
            $driverId = auth()->id();
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'type' => 'not_found',
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if order is still pending
            if ($order->status != OrderStatus::Pending) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order is no longer available',
                    'type' => 'not_found',
                ], 400);
            }

            // Record the rejection
            OrderRejection::firstOrCreate([
                'order_id' => $orderId,
                'driver_id' => $driverId,
            ]);

            \Log::info("Driver {$driverId} rejected order {$orderId}");

            return response()->json([
                'status' => true,
                'message' => 'Order rejected successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error rejecting order: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error rejecting order'
            ], 500);
        }
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
        $orders = $query->with(['user', 'service', 'driver', 'coupon'])->paginate($perPage);

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




    public function show(Request $request, $id)
    {
        $driver = Auth::guard('driver-api')->user();

        // Detect language from request header (default: ar)
        $lang = strtolower($request->header('lang', 'ar'));
        if (!in_array($lang, ['ar', 'en'])) {
            $lang = 'ar';
        }

        $order = Order::where('id', $id)
            ->where('driver_id', $driver->id)
            ->with([
                'user:id,name,phone,country_code,photo,fcm_token,balance,app_credit,app_credit_orders_remaining',
                'driver.ratings',
                'driver',
                'service',
                'coupon'
            ])
            ->first();

        if (!$order) {
            return $this->error_response('Order not found', null);
        }

        // Add helper attributes
        $order->status_text          = $order->getStatusText();
        $order->payment_method_text  = $order->getPaymentMethodText();
        $order->payment_status_text  = $order->getPaymentStatusText();
        $order->distance             = $order->getDistance();
        $order->discount_percentage  = $order->getDiscountPercentage();

        // ========== PAYMENT BREAKDOWN ==========
        $totalAmount = $order->total_price_after_discount;
        $paymentBreakdown = [];

        switch ($order->payment_method) {

            // ─── CASH ────────────────────────────────────────────────
            case PaymentMethod::Cash:
                $paymentBreakdown = [
                    'payment_type'            => 'cash',
                    'total_amount'            => $totalAmount,
                    'amount_from_wallet'      => 0,
                    'amount_from_app_credit'  => 0,
                    'amount_cash_to_collect'  => $totalAmount,
                    'message' => $lang === 'ar'
                        ? "المبلغ الكامل {$totalAmount} JD سيتم تحصيله نقداً"
                        : "The full amount of JD {$totalAmount} will be collected in cash",
                ];
                break;

            // ─── WALLET ──────────────────────────────────────────────
            case PaymentMethod::Wallet:
                $userBalance  = $order->user ? (float) $order->user->balance : 0;
                $walletAmount = min($userBalance, $totalAmount); // لا يزيد عن السعر الإجمالي
                $cashAmount   = round($totalAmount - $walletAmount, 2);

                if ($cashAmount > 0) {
                    // Hybrid: محفظة + نقد
                    $message = $lang === 'ar'
                        ? "سيتم تحويل {$walletAmount} JD من محفظة المستخدم إلى محفظتك، وستقوم بتحصيل {$cashAmount} JD نقداً"
                        : "JD {$walletAmount} will be transferred from the user's wallet to yours, and you will collect JD {$cashAmount} in cash";
                } else {
                    // Full wallet
                    $message = $lang === 'ar'
                        ? "سيتم تحويل المبلغ الكامل {$walletAmount} JD من محفظة المستخدم إلى محفظتك"
                        : "The full amount of JD {$walletAmount} will be transferred from the user's wallet to yours";
                }

                $paymentBreakdown = [
                    'payment_type'            => $cashAmount > 0 ? 'hybrid_wallet_cash' : 'full_wallet',
                    'total_amount'            => $totalAmount,
                    'amount_from_wallet'      => $walletAmount,
                    'amount_from_app_credit'  => 0,
                    'amount_cash_to_collect'  => $cashAmount,
                    'message'                 => $message,
                ];
                break;

            // ─── APP CREDIT ──────────────────────────────────────────
            case PaymentMethod::AppCredit:
                // جيب القيمة الفعلية لكل رحلة من جدول wallet_distributions
                $distribution = \App\Models\WalletDistribution::where('activate', 1)->first();
                $creditPerOrder = $distribution ? (float) $distribution->amount_per_order : 0;

                $creditUsed = min($creditPerOrder, $totalAmount); // لا يزيد عن السعر الإجمالي
                $cashAmount = round($totalAmount - $creditUsed, 2);

                if ($cashAmount > 0) {
                    // Hybrid: رصيد التطبيق + نقد
                    $message = $lang === 'ar'
                        ? "سيتم تحويل {$creditUsed} JD من رصيد التطبيق إلى محفظتك، وستقوم بتحصيل {$cashAmount} JD نقداً"
                        : "JD {$creditUsed} will be transferred from the app credit to yours, and you will collect JD {$cashAmount} in cash";
                } else {
                    // Full app credit
                    $message = $lang === 'ar'
                        ? "سيتم تحويل المبلغ الكامل {$creditUsed} JD من رصيد التطبيق إلى محفظتك"
                        : "The full amount of JD {$creditUsed} will be transferred from the app credit to yours";
                }

                $paymentBreakdown = [
                    'payment_type'            => $cashAmount > 0 ? 'hybrid_app_credit_cash' : 'full_app_credit',
                    'total_amount'            => $totalAmount,
                    'amount_from_wallet'      => 0,
                    'amount_from_app_credit'  => $creditUsed,
                    'amount_cash_to_collect'  => $cashAmount,
                    'message'                 => $message,
                ];
                break;
        }

        $responseData = $order->toArray();
        $responseData['payment_breakdown'] = $paymentBreakdown;

        return $this->success_response('Order details retrieved successfully', $responseData);
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
            'note' => "غرامة تجاوز الحد اليومي لإلغاء الطلبات",
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
                    'type' => 'not_found',
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
                    'order' => $order->load('service', 'driver', 'user'),
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

    public function updateStatus(Request $request, $id)
    {
        $driver = Auth::guard('driver-api')->user();
        $order = Order::with(['service', 'user', 'driver', 'coupon'])->where('id', $id)
            ->where('driver_id', $driver->id)
            ->first();

        if (!$order) {
            return $this->error_response('Order not found', null);
        }

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
            'live_fare' => 'required_if:status,' . OrderStatus::waitingPayment->value . '|numeric|min:0',
            'live_distance' => 'required_if:status,' . OrderStatus::waitingPayment->value . '|numeric|min:0',
            'waiting_time' => 'required_if:status,' . OrderStatus::waitingPayment->value . '|integer|min:0',
            // NEW: Optional returned amount field for Delivered status
            'returned_amount' => 'nullable|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            $statusService = app(OrderStatusService::class);
            $currentStatus = $order->status;
            $newStatus = OrderStatus::from($request->status);

            // Record status change FIRST
            $statusChange = $statusService->recordStatusChange(
                $order,
                $newStatus->value,
                $driver->id,
                'driver'
            );

            // Track when trip starts
            if ($newStatus === OrderStatus::UserWithDriver && is_null($order->trip_started_at)) {
                $order->trip_started_at = now();
            }

            // Calculate pricing for waiting payment
            $pricingDetails = null;
            if ($newStatus === OrderStatus::waitingPayment) {
                // Update drop location
                $order->drop_name = $request->input('drop_name');
                $order->drop_lat = (float) $request->input('drop_lat');
                $order->drop_lng = (float) $request->input('drop_lng');

                // Get live data from mobile
                $liveFare = (float) $request->input('live_fare');
                $liveDistance = (float) $request->input('live_distance');
                $inTripWaitingMinutes = (int) ($request->input('waiting_time') / 60);

                // Store the live distance and in-trip waiting time
                $order->live_distance = $liveDistance;
                $order->in_trip_waiting_minutes = $inTripWaitingMinutes;

                // Calculate the final price using the new method
                $pricingDetails = $this->calculateFinalPriceFromLiveData(
                    $order,
                    $liveFare,
                    $liveDistance,
                    $inTripWaitingMinutes
                );

                // Update order with calculated values
                $order->total_price_before_discount = $pricingDetails['total_before_discount'];
                $order->discount_value = $pricingDetails['discount_value'];
                $order->total_price_after_discount = $pricingDetails['final_price'];
                $order->net_price_for_driver = $pricingDetails['net_price_for_driver'];
                $order->commision_of_admin = $pricingDetails['admin_commission'];
                $order->total_waiting_minutes = $pricingDetails['driver_waiting_details']['total_waiting_minutes'];
                $order->waiting_charges = $pricingDetails['driver_waiting_details']['waiting_charges'];
                $order->in_trip_waiting_charges = $pricingDetails['in_trip_waiting_charges'];

                // Complete trip timing
                if ($order->trip_started_at && !$order->trip_completed_at) {
                    $tripCompletedAt = now();
                    $order->trip_completed_at = $tripCompletedAt;
                    $order->actual_trip_duration_minutes = $order->trip_started_at->diffInMinutes($tripCompletedAt);
                }

                $order->status = $newStatus;
                $order->save();

                Log::info("Order {$order->id}: Final price calculated from live data", [
                    'live_fare' => $liveFare,
                    'live_distance' => $liveDistance,
                    'in_trip_waiting' => $inTripWaitingMinutes,
                    'driver_waiting_charges' => $pricingDetails['driver_waiting_details']['waiting_charges'],
                    'final_price' => $pricingDetails['final_price']
                ]);
            }

            // Process payment when status is delivered
            $paymentDetails = null;
            $balanceTransferDetails = null;

            if ($newStatus === OrderStatus::Delivered) {
                // Store returned amount if provided (sent with Delivered status)
                if ($request->has('returned_amount') && $request->input('returned_amount') > 0) {
                    $order->returned_amount = (float) $request->input('returned_amount');
                    $order->save();
                }

                $result = $this->orderPaymentService->markAsDeliveredAndProcessPayment($order, $driver);

                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }

                $order = $result['order'];
                $paymentDetails = $result['payment_details'];

                $driver->refresh();

                // Process returned amount if exists and payment is cash
                if ($order->returned_amount > 0 && $order->payment_method === PaymentMethod::Cash) {
                    $balanceTransferResult = $this->processReturnedAmount($order, $driver);

                    if (!$balanceTransferResult['success']) {
                        throw new \Exception($balanceTransferResult['error']);
                    }

                    $balanceTransferDetails = $balanceTransferResult['details'];

                    Log::info("Order {$order->id}: Returned amount processed", [
                        'amount' => $order->returned_amount,
                        'driver_new_balance' => $balanceTransferDetails['driver_new_balance']
                    ]);
                }

                // ========== UPDATE USER CHALLENGES ==========
                try {
                    // Update trips challenge
                    $order->user->updateChallengeProgress('trips', 1);
                    
                    // Update spending challenge
                    if ($order->total_price_after_discount > 0) {
                        $order->user->updateChallengeProgress('spending', $order->total_price_after_discount);
                    }
                    
                    Log::info("User {$order->user_id} challenges updated for order {$order->id}");
                } catch (\Exception $e) {
                    Log::error("Error updating user challenges: " . $e->getMessage());
                    // Don't throw error, just log it
                }
                // ========== END UPDATE USER CHALLENGES ==========
            } else if ($newStatus !== OrderStatus::waitingPayment) {
                // Update status for other cases
                $order->status = $newStatus;
                $order->save();
            }

            DB::commit();

            // Send notifications
            EnhancedFCMService::sendOrderStatusToUser($id, $newStatus);

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
                'status_change' => [
                    'previous_status' => $statusChange['previous_status'],
                    'new_status' => $newStatus->value,
                    'duration_from_previous' => $statusChange['duration_minutes'],
                    'changed_at' => $statusChange['changed_at']->format('Y-m-d H:i:s')
                ]
            ];

            if ($pricingDetails) {
                $responseData['pricing_details'] = $pricingDetails;
            }

            if ($paymentDetails) {
                $responseData['payment_details'] = $paymentDetails;
            }

            if ($balanceTransferDetails) {
                $responseData['balance_transfer'] = $balanceTransferDetails;
            }

            return $this->success_response('Order status updated successfully', $responseData);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order status: ' . $e->getMessage());

            return $this->error_response('Error updating order status', $e->getMessage());
        }
    }

    private function processReturnedAmount($order, $driver)
    {
        try {
            $amount = $order->returned_amount;

            // Validate user exists
            if (!$order->user) {
                return [
                    'success' => false,
                    'error' => 'User not found for this order'
                ];
            }

            // Get fresh driver data from database
            $driverModel = Driver::find($driver->id);

            // Check if driver has sufficient balance
            if ($driverModel->balance < $amount) {
                return [
                    'success' => false,
                    'error' => 'Insufficient balance in driver wallet. Balance: ' . $driverModel->balance . ', Required: ' . $amount
                ];
            }

            // Deduct amount from driver's wallet balance
            $driverModel->decrement('balance', $amount);

            // Add amount to user's wallet balance
            $order->user->increment('balance', $amount);

            // Create wallet transaction record for USER (add balance)
            $userTransaction = WalletTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'driver_id' => null,
                'admin_id' => null,
                'amount' => $amount,
                'type_of_transaction' => 1, // 1 = add
                'note' => "الباقي المُعاد من السائق للطلب رقم {$order->number}",
            ]);

            // Create wallet transaction record for DRIVER (withdrawal)
            $driverTransaction = WalletTransaction::create([
                'order_id' => $order->id,
                'user_id' => null,
                'driver_id' => $driver->id,
                'admin_id' => null,
                'amount' => $amount,
                'type_of_transaction' => 2, // 2 = withdrawal
                'note' => "الباقي المدفوع للمستخدم للطلب رقم {$order->number}",
            ]);

            // Get updated balance
            $driverModel->refresh();

            return [
                'success' => true,
                'details' => [
                    'amount' => $amount,
                    'user_id' => $order->user_id,
                    'user_name' => $order->user->name,
                    'driver_new_balance' => $driverModel->balance,
                    'user_transaction_id' => $userTransaction->id,
                    'driver_transaction_id' => $driverTransaction->id,
                    'processed_at' => now()->format('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to process returned amount: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate final price from live data sent by mobile
     * 
     * @param Order $order
     * @param float $liveFare - Real total price from mobile (distance-based fare)
     * @param float $liveDistance - Real distance traveled in km
     * @param int $inTripWaitingMinutes - Waiting time during trip (traffic stops, etc.)
     * @return array
     */
    private function calculateFinalPriceFromLiveData(Order $order, float $liveFare, float $liveDistance, int $inTripWaitingMinutes)
    {
        $service = $order->service;

        // Step 1: Start with the live fare from mobile 
        // This ALREADY includes: base distance fare + in-trip waiting charges
        $baseFare = $liveFare;

        // Step 2: Calculate driver waiting charges (when driver arrived but user hasn't started trip yet)
        // This is the ONLY charge we add on backend
        $driverWaitingDetails = $this->calculateDriverWaitingCharges($order);
        $driverWaitingCharges = $driverWaitingDetails['waiting_charges'];

        // Step 3: Calculate in-trip waiting charges FOR RECORD ONLY (not added to price)
        // Mobile already included this in live_fare, we just store it for transparency
        $chargePerMinuteInTrip = $service->waiting_charge_per_minute_when_order_active ?? 0;
        $inTripWaitingCharges = $inTripWaitingMinutes * $chargePerMinuteInTrip;

        // Step 4: Calculate total price before discount
        // Only add driver waiting charges (in-trip waiting is already in liveFare)
        $totalBeforeDiscount = $baseFare + $driverWaitingCharges;

        // Step 5: Apply coupon discount if applicable
        $discountDetails = $this->applyCouponDiscount($order, $totalBeforeDiscount);
        $discountValue = $discountDetails['discount_value'];
        $finalPrice = $totalBeforeDiscount - $discountValue;

        // Step 6: Calculate commission
        $commissionData = $this->orderPaymentService->getServiceCommission($service->id, $finalPrice);
        $adminCommission = $commissionData['admin_commission'];
        $netPriceForDriver = $finalPrice - $adminCommission;

        return [
            'live_fare_from_mobile' => $baseFare,
            'live_distance_km' => $liveDistance,
            'driver_waiting_details' => $driverWaitingDetails,
            'in_trip_waiting_minutes' => $inTripWaitingMinutes,
            'in_trip_waiting_charge_per_minute' => $chargePerMinuteInTrip,
            'in_trip_waiting_charges' => round($inTripWaitingCharges, 2), // For record only
            'in_trip_waiting_note' => 'Already included in live_fare from mobile',
            'total_before_discount' => round($totalBeforeDiscount, 2),
            'coupon_applied' => $discountDetails['coupon_applied'],
            'coupon_details' => $discountDetails['coupon_details'],
            'discount_value' => round($discountValue, 2),
            'final_price' => round($finalPrice, 2),
            'commission_type' => $commissionData['type_text'],
            'commission_value' => $commissionData['commission_value'],
            'admin_commission' => round($adminCommission, 2),
            'net_price_for_driver' => round($netPriceForDriver, 2),
            'price_breakdown' => [
                'live_fare_from_mobile' => round($baseFare, 2),
                'driver_waiting_charges_added' => round($driverWaitingCharges, 2),
                'subtotal' => round($totalBeforeDiscount, 2),
                'discount' => round($discountValue, 2),
                'total' => round($finalPrice, 2),
                'note' => 'In-trip waiting charges are already included in live_fare from mobile'
            ]
        ];
    }

    /**
     * Calculate driver waiting charges based on order_status_histories table
     * This is the time driver waited for user after arriving (arrived -> started)
     */
    private function calculateDriverWaitingCharges(Order $order)
    {
        $service = $order->service;
        $freeWaitingMinutes = $service->free_waiting_minutes ?? 3;
        $chargePerMinute = $service->waiting_charge_per_minute ?? 0;

        // Get the timestamps from order_status_histories
        $arrivedHistory = OrderStatusHistory::where('order_id', $order->id)
            ->where('status', 'arrived')
            ->first();

        $startedHistory = OrderStatusHistory::where('order_id', $order->id)
            ->where('status', 'started')
            ->first();

        if (!$arrivedHistory || !$startedHistory) {
            // If we can't find the history, use the order's arrived_at if available
            if ($order->arrived_at && $order->trip_started_at) {
                $totalWaitingMinutes = Carbon::parse($order->arrived_at)->diffInMinutes($order->trip_started_at);
            } else {
                return [
                    'total_waiting_minutes' => 0,
                    'free_waiting_minutes' => $freeWaitingMinutes,
                    'billable_waiting_minutes' => 0,
                    'charge_per_minute' => $chargePerMinute,
                    'waiting_charges' => 0,
                    'source' => 'no_history_found'
                ];
            }
        } else {
            // Calculate waiting time from status history
            $totalWaitingMinutes = Carbon::parse($arrivedHistory->changed_at)
                ->diffInMinutes(Carbon::parse($startedHistory->changed_at));
        }

        // Calculate billable minutes (after free period)
        $billableWaitingMinutes = max(0, $totalWaitingMinutes - $freeWaitingMinutes);
        $waitingCharges = $billableWaitingMinutes * $chargePerMinute;

        return [
            'total_waiting_minutes' => $totalWaitingMinutes,
            'free_waiting_minutes' => $freeWaitingMinutes,
            'billable_waiting_minutes' => $billableWaitingMinutes,
            'charge_per_minute' => $chargePerMinute,
            'waiting_charges' => round($waitingCharges, 2),
            'source' => 'order_status_histories'
        ];
    }

    /**
     * Apply coupon discount to the total price
     */
    private function applyCouponDiscount(Order $order, float $totalPrice)
    {
        if (!$order->coupon_id || !$order->coupon) {
            return [
                'coupon_applied' => false,
                'discount_value' => 0,
                'coupon_details' => null
            ];
        }

        $coupon = $order->coupon;

        // Check if coupon is still valid
        if (!$coupon->isValid()) {
            Log::info("Order {$order->id}: Coupon is no longer valid");
            return [
                'coupon_applied' => false,
                'discount_value' => 0,
                'coupon_details' => [
                    'reason' => 'Coupon expired or inactive'
                ]
            ];
        }

        // Check minimum amount requirement
        if ($totalPrice < $coupon->minimum_amount) {
            Log::info("Order {$order->id}: Total price {$totalPrice} below coupon minimum {$coupon->minimum_amount}");
            return [
                'coupon_applied' => false,
                'discount_value' => 0,
                'coupon_details' => [
                    'reason' => 'Total price below minimum amount',
                    'minimum_required' => $coupon->minimum_amount,
                    'current_total' => $totalPrice
                ]
            ];
        }

        // Calculate discount based on type
        if ($coupon->discount_type == 1) {
            // Fixed amount discount
            $discountValue = $coupon->discount;
        } else {
            // Percentage discount
            $discountValue = ($totalPrice * $coupon->discount) / 100;
        }

        // Ensure discount doesn't exceed total price
        $discountValue = min($discountValue, $totalPrice);

        Log::info("Order {$order->id}: Coupon applied - discount {$discountValue}");

        return [
            'coupon_applied' => true,
            'discount_value' => round($discountValue, 2),
            'coupon_details' => [
                'coupon_id' => $coupon->id,
                'discount_type' => $coupon->discount_type == 1 ? 'fixed' : 'percentage',
                'discount_value' => $coupon->discount,
                'calculated_discount' => round($discountValue, 2)
            ]
        ];
    }
}
