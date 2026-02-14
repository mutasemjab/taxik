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
use Illuminate\Support\Facades\Http;

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

    public function updateOrderRadius(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $radius = $request->radius;

            $order = Order::find($orderId);

            // ✅ Strict validation before proceeding
            if (!$order) {
                \Log::warning("updateOrderRadius: Order {$orderId} not found");
                return response()->json(['success' => false, 'reason' => 'order_not_found']);
            }

            if ($order->status != OrderStatus::Pending) {
                \Log::info("updateOrderRadius: Order {$orderId} no longer pending (status: {$order->status->value})");
                return response()->json(['success' => false, 'reason' => 'order_not_pending']);
            }

            if ($order->driver_id) {
                \Log::info("updateOrderRadius: Order {$orderId} already has driver {$order->driver_id}");
                return response()->json(['success' => false, 'reason' => 'driver_already_assigned']);
            }

            // This runs in web context - has gRPC!
            $driverLocationService = app(\App\Services\DriverLocationService::class);

            $result = $driverLocationService->findAndStoreOrderInFirebase(
                $request->user_lat,
                $request->user_lng,
                $orderId,
                $request->service_id,
                $radius * 1000, // Convert km to meters
                OrderStatus::Pending->value
            );

            \Log::info("Updated Firebase for order {$orderId} at {$radius}km via HTTP. Result: " . ($result['success'] ? 'success' : 'failed'));

            return response()->json([
                'success' => $result['success'],
                'result' => $result,
                'drivers_found' => $result['drivers_found'] ?? 0
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in updateOrderRadius for order {$orderId}: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
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
                $distance = 0;

                if (!is_null($request->end_lat) && !is_null($request->end_lng)) {
                    $distance = $this->calculateDistanceOSRM(
                        $request->start_lat,
                        $request->start_lng,
                        $request->end_lat,
                        $request->end_lng
                    );
                }

                $isEvening = $this->isEveningTime();
                $startPrice = $isEvening ? $service->start_price_evening : $service->start_price_morning;
                $pricePerKm = $isEvening ? $service->price_per_km_evening : $service->price_per_km_morning;

                $calculatedPrice = $startPrice + ($pricePerKm * $distance);
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

                if (!$coupon->isValid()) {
                    return response()->json([
                        'status' => false,
                        'type' => 'expired',
                        'message' => 'Coupon has expired or not yet active'
                    ], 200);
                }

                $userUsageCount = DB::table('user_coupons')
                    ->where('user_id', auth()->id())
                    ->where('coupon_id', $coupon->id)
                    ->count();

                if (!is_null($coupon->number_of_used) && $userUsageCount >= $coupon->number_of_used) {
                    return response()->json([
                        'status' => false,
                        'type' => 'coupon_usage_limit_reached',
                        'message' => 'You have reached the maximum usage limit for this coupon'
                    ], 200);
                }

                if ($calculatedPrice < $coupon->minimum_amount) {
                    return response()->json([
                        'status' => false,
                        'type' => 'minimum_amount',
                        'message' => 'Order amount does not meet minimum requirement for this coupon'
                    ], 200);
                }

                if ($coupon->coupon_type == 2) {
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
                } elseif ($coupon->coupon_type == 3) {
                    if ($coupon->service_id != $request->service_id) {
                        return response()->json([
                            'status' => false,
                            'type' => 'coupon_invalid_service',
                            'message' => 'This coupon is not valid for the selected service'
                        ], 200);
                    }
                }

                if ($coupon->discount_type == 1) {
                    $discountValue = $coupon->discount;
                } else {
                    $discountValue = ($calculatedPrice * $coupon->discount) / 100;
                }

                $discountValue = min($discountValue, $calculatedPrice);
                $finalPrice = $calculatedPrice - $discountValue;
                $couponId = $coupon->id;
            }

            // ========== ✅ BALANCE CHECK FOR APP CREDIT - Only check for amount_per_order ==========
            $user = auth()->user();

            if ($paymentMethodValue === PaymentMethod::AppCredit->value) {
                // ✅ التحقق من توفر المبلغ المحدد لكل رحلة فقط (0.5 JD مثلاً)
                $appCreditEnabled = DB::table('settings')
                    ->where('key', 'enable_app_credit_distribution_system')
                    ->value('value') == 1;

                if (!$appCreditEnabled) {
                    return response()->json([
                        'status' => false,
                        'type' => 'app_credit_disabled',
                        'message' => 'نظام رصيد التطبيق غير مفعل حالياً',
                    ], 200);
                }

                $availableAppCreditPerOrder = $user->getAvailableAppCreditForOrder();

                // ✅ فقط نتحقق من وجود رصيد للرحلة (0.5 JD)، مش من السعر الكلي
                if ($availableAppCreditPerOrder <= 0) {
                    return response()->json([
                        'status' => false,
                        'type' => 'no_app_credit_available',
                        'message' => 'لا يوجد رصيد تطبيق متاح لهذه الرحلة',
                        'data' => [
                            'app_credit_available_per_order' => $availableAppCreditPerOrder,
                            'app_credit_orders_remaining' => $user->app_credit_orders_remaining,
                            'app_credit_amount_per_order' => $user->app_credit_amount_per_order,
                            'total_order_price' => $finalPrice,
                            'will_pay_from_app_credit' => 0,
                            'will_pay_cash' => $finalPrice,
                        ]
                    ], 200);
                }

                // ✅ حساب كم سيدفع من رصيد التطبيق وكم نقدي
                $amountFromAppCredit = min($availableAppCreditPerOrder, $finalPrice);
                $cashAmount = max(0, $finalPrice - $amountFromAppCredit);
            } elseif ($paymentMethodValue === PaymentMethod::Wallet->value) {
                // ✅ التحقق من المحفظة الحقيقية فقط
                $realWalletBalance = $user->balance;

                if ((float) $realWalletBalance <= 0) {
                    return response()->json([
                        'status' => false,
                        'type' => 'insufficient_wallet_balance',
                        'message' => 'رصيد المحفظة غير كافٍ',
                        'data' => [
                            'wallet_balance' => $realWalletBalance,
                            'required_amount' => $finalPrice,
                            'shortage' => $finalPrice - $realWalletBalance,
                        ]
                    ], 200);
                }
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

                // Record coupon usage if applied
                if ($couponId) {
                    DB::table('user_coupons')->insert([
                        'coupon_id' => $couponId,
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::commit();

                // Start driver search
                $result = $this->driverLocationService->findAndStoreOrderInFirebase(
                    $request->start_lat,
                    $request->start_lng,
                    $order->id,
                    $request->service_id,
                    null,
                    OrderStatus::Pending->value,
                    false
                );

                $searchEnded = ($result['next_radius'] === null);

                if ($searchEnded) {
                    $this->driverLocationService->updateEndSearchFlag($order->id, true);
                }

                if (isset($result['next_radius']) && $result['next_radius'] !== null) {
                    \Log::info("Driver search will continue - next zone: {$result['next_radius']}km for order {$order->id}");
                } elseif (isset($result['search_complete']) && $result['search_complete'] === true) {
                    \Log::info("Driver search completed - all zones searched for order {$order->id}");
                }

                // ✅ إضافة معلومات الدفع بالنسبة لرصيد التطبيق
                $paymentBreakdown = null;
                if ($paymentMethodValue === PaymentMethod::AppCredit->value) {
                    $paymentBreakdown = [
                        'payment_method' => 'app_credit',
                        'total_order_price' => $finalPrice,
                        'app_credit_available_per_order' => $availableAppCreditPerOrder,
                        'amount_from_app_credit' => $amountFromAppCredit,
                        'amount_cash_required' => $cashAmount,
                        'orders_remaining' => $user->app_credit_orders_remaining,
                        'message_ar' => $cashAmount > 0
                            ? "سيتم خصم {$amountFromAppCredit} JD من رصيد التطبيق والباقي {$cashAmount} JD نقداً"
                            : "سيتم خصم {$amountFromAppCredit} JD من رصيد التطبيق",
                        'message_en' => $cashAmount > 0
                            ? "JD {$amountFromAppCredit} will be deducted from app credit and JD {$cashAmount} cash"
                            : "JD {$amountFromAppCredit} will be deducted from app credit"
                    ];
                }

                return response()->json([
                    'status' => true,
                    'message' => $result['success']
                        ? 'Order created successfully. Searching for drivers in ' . ($result['search_radius'] ?? 5) . 'km radius.'
                        : $result['message'],
                    'data' => [
                        'order' => $order->load(['service', 'coupon']),
                        'service' => $service,
                        'coupon_applied' => $couponId ? true : false,
                        'discount_applied' => $discountValue,
                        'payment_breakdown' => $paymentBreakdown, // ✅ معلومات الدفع
                        'driver_search' => [
                            'drivers_found' => $result['drivers_found'] ?? 0,
                            'current_search_radius' => $result['search_radius'] ?? 5,
                            'next_search_radius' => $result['next_radius'] ?? null,
                            'will_expand_search' => ($result['next_radius'] ?? null) !== null,
                            'wait_time_seconds' => 30,
                            'end_search' => $searchEnded,
                            'status' => $result['success'] ? 'searching' : 'no_drivers_available'
                        ],
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

    /**
     * Determine if current time is evening
     * Evening: 22:00 (10 PM) to 06:00 (6 AM)
     */
    private function isEveningTime($dateTime = null)
    {
        $checkTime = $dateTime ?? now();
        $hour = $checkTime->format('H');
        return $hour >= 22 || $hour < 6;
    }

    private function calculateDistanceFallback($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radius in kilometers

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));

        return $earthRadius * $angle;
    }

    private function calculateDistanceOSRM($lat1, $lng1, $lat2, $lng2)
    {
        try {
            // OSRM format: longitude,latitude (reversed!)
            $url = "https://router.project-osrm.org/route/v1/driving/"
                . "{$lng1},{$lat1};"
                . "{$lng2},{$lat2}"
                . "?overview=false&alternatives=false&steps=false";

            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['code'] === 'Ok' && isset($data['routes'][0]['distance'])) {
                    // Distance is in meters, convert to kilometers
                    $distanceInMeters = $data['routes'][0]['distance'];
                    return $distanceInMeters / 1000;
                }
            }

            // If OSRM fails, fallback to Haversine
            \Log::warning("OSRM failed for order distance calculation, using fallback");
            return $this->calculateDistanceFallback($lat1, $lng1, $lat2, $lng2);
        } catch (\Exception $e) {
            // On exception, fallback to Haversine
            \Log::warning("OSRM exception in order: " . $e->getMessage() . ", using fallback");
            return $this->calculateDistanceFallback($lat1, $lng1, $lat2, $lng2);
        }
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
        $orders = $query->with(['driver', 'service', 'coupon'])->paginate($perPage);

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

    public function show(Request $request, $id)
    {
        $user = Auth::user();

        // Detect language from request header (default: ar)
        $lang = strtolower($request->header('lang', 'ar'));
        if (!in_array($lang, ['ar', 'en'])) {
            $lang = 'ar';
        }

        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with([
                'driver',
                'driver.ratings',
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
        $order->tracking_url         = $order->getTrackingUrl();

        $hasRated = \App\Models\Rating::where('user_id', $user->id)
            ->where('order_id', $order->id)
            ->where('driver_id', $order->driver_id)
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

        // ========== PAYMENT BREAKDOWN ==========
        $totalAmount    = $order->total_price_after_discount;
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
                        ? "المبلغ الكامل {$totalAmount} JD سيتم دفعه نقداً"
                        : "The full amount of JD {$totalAmount} will be paid in cash",
                ];
                break;

            // ─── WALLET ──────────────────────────────────────────────
            case PaymentMethod::Wallet:
                $userBalance  = (float) $user->balance;
                $walletAmount = min($userBalance, $totalAmount);
                $cashAmount   = round($totalAmount - $walletAmount, 2);

                if ($cashAmount > 0) {
                    $message = $lang === 'ar'
                        ? "سيتم خصم {$walletAmount} JD من محفظتك والمبلغ المتبقي {$cashAmount} JD سيتم دفعه نقداً"
                        : "JD {$walletAmount} will be deducted from your wallet and the remaining JD {$cashAmount} will be paid in cash";
                } else {
                    $message = $lang === 'ar'
                        ? "سيتم خصم المبلغ الكامل {$walletAmount} JD من محفظتك"
                        : "The full amount of JD {$walletAmount} will be deducted from your wallet";
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
                $distribution   = \App\Models\WalletDistribution::where('activate', 1)->first();
                $creditPerOrder = $distribution ? (float) $distribution->amount_per_order : 0;

                $creditUsed = min($creditPerOrder, $totalAmount);
                $cashAmount = round($totalAmount - $creditUsed, 2);

                if ($cashAmount > 0) {
                    $message = $lang === 'ar'
                        ? "سيتم خصم {$creditUsed} JD من رصيد التطبيق والمبلغ المتبقي {$cashAmount} JD سيتم دفعه نقداً"
                        : "JD {$creditUsed} will be deducted from your app credit and the remaining JD {$cashAmount} will be paid in cash";
                } else {
                    $message = $lang === 'ar'
                        ? "سيتم خصم المبلغ الكامل {$creditUsed} JD من رصيد التطبيق"
                        : "The full amount of JD {$creditUsed} will be deducted from your app credit";
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
        $user = Auth::user();

        $order = Order::with('service')->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'type' => 'not_found',
                'message' => 'Order not found'
            ], 200);
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

            $cancellationFeeApplied = false;
            $cancellationFeeAmount = 0;

            // ✅ NEW: Check cancellation count and apply fee if exceeded
            if ($isDriverAccepted) {
                // Get settings
                $maxCancellations = (int) DB::table('settings')
                    ->where('key', 'times_that_user_cancel_orders_in_one_day')
                    ->value('value') ?? 2;

                $cancellationFee = (float) DB::table('settings')
                    ->where('key', 'fee_when_user_cancel_order_more_times')
                    ->value('value') ?? 0.5;

                // Count today's cancellations
                $todayCancellations = Order::where('user_id', $user->id)
                    ->where('status', OrderStatus::UserCancelOrder)
                    ->whereDate('updated_at', today())
                    ->count();

                \Log::info("User {$user->id} cancellations today: {$todayCancellations}, max allowed: {$maxCancellations}");

                // Apply fee if exceeded limit
                if ($todayCancellations >= $maxCancellations && $cancellationFee > 0) {
                    $this->deductCancellationFee($user->id, $order->id, $cancellationFee);
                    $cancellationFeeApplied = true;
                    $cancellationFeeAmount = $cancellationFee;

                    \Log::info("Cancellation fee of {$cancellationFee} JD applied to user {$user->id} for order {$order->id}");
                }
            }

            // ✅ Update status FIRST (stops all jobs)
            $order->status = OrderStatus::UserCancelOrder;
            $order->reason_for_cancel = $request->reason_for_cancel;
            $order->save();

            if ($isPendingOrder) {
                // ✅ Move to spam table (creates backup)
                $spamOrder = $this->moveOrderToSpamTable($order, $request->reason_for_cancel);

                // ✅ Delete the order (notifications remain)
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
                    'remaining_balance' => $user->fresh()->balance,
                    'cancellations_today' => Order::where('user_id', $user->id)
                        ->where('status', OrderStatus::UserCancelOrder)
                        ->whereDate('updated_at', today())
                        ->count()
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

    // ✅ NEW: Helper method to remove order from Firebase
    private function removeOrderFromFirebase($orderId)
    {
        try {
            $projectId = config('firebase.project_id');
            $baseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents";

            $response = Http::timeout(5)->delete("{$baseUrl}/ride_requests/{$orderId}");

            if ($response->successful()) {
                \Log::info("Order {$orderId} removed from Firebase");
            } else {
                \Log::warning("Failed to remove order {$orderId} from Firebase: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error("Error removing order {$orderId} from Firebase: " . $e->getMessage());
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
            'note' => "رسوم إلغاء الطلب بعد قبول السائق",
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
            'original_order_id' => $order->id,
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
        $distance = $this->calculateDistanceFallback($lat1, $lng1, $lat2, $lng2);

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
}
