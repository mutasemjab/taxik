<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Driver;
use App\Models\Service;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:order-table')->only('index', 'show', 'filter', 'userOrders', 'driverOrders');
        $this->middleware('permission:order-add')->only('create', 'store');
        $this->middleware('permission:order-edit')->only('edit', 'update', 'updateStatus', 'updatePaymentStatus');
        $this->middleware('permission:order-delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'driver', 'service', 'coupon', 'complaints', 'rating'])
            ->orderBy('created_at', 'desc');

        // Apply filters if they exist
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status_payment')) {
            $query->where('status_payment', $request->status_payment);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by hybrid payment
        if ($request->filled('is_hybrid_payment')) {
            $query->where('is_hybrid_payment', $request->is_hybrid_payment);
        }

        // Calculate statistics BEFORE pagination using the same filtered query
        $statistics = [
            'total_orders' => $query->count(),
            'completed_orders' => (clone $query)->where('status', 'completed')->count(),
            'cancelled_orders' => (clone $query)->whereIn('status', ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'])->count(),
            'total_revenue' => (clone $query)->where('status', 'completed')->sum('commision_of_admin'),
        ];

        // Paginate results AFTER calculating statistics
        $orders = $query->paginate(15)->withQueryString();

        // Get users, drivers, and services for filter dropdowns
        $users = User::select('id', 'name', 'phone', 'email')->get();
        $drivers = Driver::select('id', 'name', 'phone', 'email')->get();
        $services = Service::get();

        return view('admin.orders.index', compact('orders', 'users', 'drivers', 'services', 'statistics'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'driver',
            'service',
            'coupon',
            'complaints' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'rating'
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $users = User::all();
        $drivers = Driver::all();
        $services = Service::all();
        $coupons = Coupon::all();
        return view('admin.orders.edit', compact('order', 'users', 'drivers', 'services', 'coupons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'service_id' => 'required|exists:services,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'number' => 'nullable|string',
            'estimated_time' => 'nullable|string',

            'pick_name' => 'required|string|max:255',
            'pick_lat' => 'required|numeric',
            'pick_lng' => 'required|numeric',
            'drop_name' => 'nullable|string|max:255',
            'drop_lat' => 'nullable|numeric',
            'drop_lng' => 'nullable|numeric',

            'total_price_before_discount' => 'required|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'total_price_after_discount' => 'required|numeric|min:0',
            'net_price_for_driver' => 'required|numeric|min:0',
            'commision_of_admin' => 'required|numeric|min:0',

            // Hybrid payment fields
            'wallet_amount_used' => 'nullable|numeric|min:0',
            'cash_amount_due' => 'nullable|numeric|min:0',
            'is_hybrid_payment' => 'nullable|boolean',

            'trip_started_at' => 'nullable|date',
            'trip_completed_at' => 'nullable|date|after_or_equal:trip_started_at',
            'actual_trip_duration_minutes' => 'nullable|numeric|min:0',
            'live_distance' => 'nullable|numeric|min:0',
            'returned_amount' => 'nullable|numeric|min:0',

            'status' => ['required', Rule::in([
                'pending',
                'accepted',
                'on_the_way',
                'started',
                'waiting_payment',
                'completed',
                'user_cancel_order',
                'driver_cancel_order',
                'arrived',
                'cancel_cron_job'
            ])],
            'reason_for_cancel' => 'nullable|string',

            'payment_method' => ['required', Rule::in(['cash', 'visa', 'wallet'])],
            'status_payment' => ['required', Rule::in(['pending', 'paid'])],

            'arrived_at' => 'nullable|date',
            'total_waiting_minutes' => 'nullable|integer|min:0',
            'waiting_charges' => 'nullable|numeric|min:0',
            'in_trip_waiting_minutes' => 'nullable|integer|min:0',
            'in_trip_waiting_charges' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle hybrid payment checkbox
        $data['is_hybrid_payment'] = $request->has('is_hybrid_payment') ? 1 : 0;

        $order->update($data);


        // ✅ Sync to Firestore
        $this->syncOrderToFirestore($order->fresh());

        return redirect()
            ->route('orders.index')
            ->with('success', __('messages.Order_Updated_Successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', __('messages.Order_Deleted_Successfully'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in([
                'pending',
                'accepted',
                'on_the_way',
                'started',
                'waiting_payment',
                'completed',
                'user_cancel_order',
                'driver_cancel_order',
                'arrived',
                'cancel_cron_job'
            ])],
            'reason_for_cancel' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.show', $id)
                ->withErrors($validator);
        }

        $updateData = ['status' => $request->status];

        if (
            in_array($request->status, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'])
            && $request->has('reason_for_cancel')
        ) {
            $updateData['reason_for_cancel'] = $request->reason_for_cancel;
        }

        $order->update($updateData);

        // ✅ Sync to Firestore
        $this->syncOrderToFirestore($order->fresh());

        return redirect()
            ->route('orders.show', $id)
            ->with('success', __('messages.Order_Status_Updated'));
    }

    /**
     * Update the payment status.
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status_payment' => ['required', Rule::in(['pending', 'paid'])],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.show', $id)
                ->withErrors($validator);
        }

        $order->update([
            'status_payment' => $request->status_payment
        ]);


        // ✅ Sync to Firestore
        $this->syncOrderToFirestore($order->fresh());

        return redirect()
            ->route('orders.show', $id)
            ->with('success', __('messages.Payment_Status_Updated'));
    }

    private function syncOrderToFirestore(Order $order)
    {
        try {
            $projectId = config('firebase.project_id');
            $baseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents";

            // Check if document exists in Firestore first
            $getResponse = \Http::timeout(10)->get("{$baseUrl}/ride_requests/{$order->id}");

            if ($getResponse->status() === 404) {
                // Document doesn't exist, nothing to sync
                return;
            }

            $statusValue = is_object($order->status) ? $order->status->value : $order->status;
            $paymentMethod = is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method;
            $paymentStatus = is_object($order->status_payment) ? $order->status_payment->value : $order->status_payment;

            $isFinished = in_array($statusValue, [
                'completed',
                'user_cancel_order',
                'driver_cancel_order',
                'cancel_cron_job'
            ]);

            $firestoreData = [
                'fields' => [
                    'status' => ['stringValue' => $statusValue],
                    'assigned_driver_id' => $order->driver_id
                        ? ['integerValue' => (string)$order->driver_id]
                        : ['nullValue' => null],
                    'payment_info' => [
                        'mapValue' => [
                            'fields' => [
                                'payment_method' => ['stringValue' => $paymentMethod],
                                'payment_status'  => ['stringValue' => $paymentStatus],
                            ]
                        ]
                    ],
                    'pricing' => [
                        'mapValue' => [
                            'fields' => [
                                'total_price_before_discount' => ['doubleValue' => (float)$order->total_price_before_discount],
                                'discount_value'              => ['doubleValue' => (float)($order->discount_value ?? 0)],
                                'total_price_after_discount'  => ['doubleValue' => (float)$order->total_price_after_discount],
                                'net_price_for_driver'        => ['doubleValue' => (float)$order->net_price_for_driver],
                                'commission_of_admin'         => ['doubleValue' => (float)$order->commision_of_admin],
                            ]
                        ]
                    ],
                    'reason_for_cancel'  => ['stringValue' => $order->reason_for_cancel ?? ''],
                    'end_search'         => ['booleanValue' => $isFinished],
                    'firebase_updated_at' => ['timestampValue' => now()->toIso8601String()],
                ]
            ];

            $fields = [
                'status',
                'assigned_driver_id',
                'payment_info',
                'pricing',
                'reason_for_cancel',
                'end_search',
                'firebase_updated_at',
            ];

            $updateMask = implode('&', array_map(
                fn($f) => "updateMask.fieldPaths={$f}",
                $fields
            ));

            $response = \Http::timeout(10)->patch(
                "{$baseUrl}/ride_requests/{$order->id}?{$updateMask}",
                $firestoreData
            );

            if ($response->successful()) {
                \Log::info("Order #{$order->id} synced to Firestore from admin panel");
            } else {
                \Log::error("Failed to sync order #{$order->id} to Firestore: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error("Error syncing order #{$order->id} to Firestore: " . $e->getMessage());
        }
    }
}
