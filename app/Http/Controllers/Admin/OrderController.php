<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\User;
use App\Models\Driver;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StatusPayment;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::with(['user', 'driver', 'service'])->orderBy('created_at', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $drivers = Driver::all();
        $services = Service::all();
        return view('admin.orders.create', compact('users', 'drivers', 'services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'service_id' => 'required|exists:services,id',
            'pick_name' => 'required|string|max:255',
            'pick_lat' => 'required|numeric',
            'pick_lng' => 'required|numeric',
            'drop_name' => 'required|string|max:255',
            'drop_lat' => 'required|numeric',
            'drop_lng' => 'required|numeric',
            'total_price_before_discount' => 'required|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'total_price_after_discount' => 'required|numeric|min:0',
            'net_price_for_driver' => 'required|numeric|min:0',
            'commision_of_admin' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'reason_for_cancel' => 'nullable|required_if:status,' . OrderStatus::UserCancelOrder->value . ',' . OrderStatus::DriverCancelOrder->value . '|string',
            'payment_method' => ['required', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
            'status_payment' => ['required', Rule::in(array_column(StatusPayment::cases(), 'value'))],
            // NEW: Waiting charges validation
            'arrived_at' => 'nullable|date',
            'total_waiting_minutes' => 'nullable|integer|min:0',
            'waiting_charges' => 'nullable|numeric|min:0',
            'in_trip_waiting_minutes' => 'nullable|integer|min:0',
            'in_trip_waiting_charges' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.create')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Cast enum fields manually
        $data['status'] = OrderStatus::from($data['status']);
        $data['payment_method'] = PaymentMethod::from($data['payment_method']);
        $data['status_payment'] = StatusPayment::from($data['status_payment']);

        Order::create($data);

        return redirect()
            ->route('orders.index')
            ->with('success', __('messages.Order_Created_Successfully'));
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::with(['user', 'driver', 'service'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $users = User::all();
        $drivers = Driver::all();
        $services = Service::all();
        return view('admin.orders.edit', compact('order', 'users', 'drivers', 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'service_id' => 'required|exists:services,id',
            'pick_name' => 'required|string|max:255',
            'pick_lat' => 'required|numeric',
            'pick_lng' => 'required|numeric',
            'drop_name' => 'required|string|max:255',
            'drop_lat' => 'required|numeric',
            'drop_lng' => 'required|numeric',
            'total_price_before_discount' => 'required|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'total_price_after_discount' => 'required|numeric|min:0',
            'net_price_for_driver' => 'required|numeric|min:0',
            'commision_of_admin' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'reason_for_cancel' => 'nullable|required_if:status,' . OrderStatus::UserCancelOrder->value . ',' . OrderStatus::DriverCancelOrder->value . '|string',
            'payment_method' => ['required', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
            'status_payment' => ['required', Rule::in(array_column(StatusPayment::cases(), 'value'))],
            // NEW: Waiting charges validation
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

        $data['status'] = OrderStatus::from($data['status']);
        $data['payment_method'] = PaymentMethod::from($data['payment_method']);
        $data['status_payment'] = StatusPayment::from($data['status_payment']);

        $order->update($data);

        return redirect()
            ->route('orders.index')
            ->with('success', __('messages.Order_Updated_Successfully'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * Filter orders by various criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'service_id' => 'nullable|exists:services,id',
            'status' => 'nullable|in:all,1,2,3,4,5,6,7',
            'payment_method' => 'nullable|in:all,1,2,3',
            'status_payment' => 'nullable|in:all,1,2',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.index')
                ->withErrors($validator);
        }

        $query = Order::with(['user', 'driver', 'service']);

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by driver
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by service
        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by status
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->payment_method && $request->payment_method != 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by payment status
        if ($request->status_payment && $request->status_payment != 'all') {
            $query->where('status_payment', $request->status_payment);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get the filtered orders
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        // Get users, drivers and services for the filter dropdowns
        $users = User::all();
        $drivers = Driver::all();
        $services = Service::all();

        return view('admin.orders.index', compact('orders', 'users', 'drivers', 'services'));
    }

    /**
     * Update the order status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,2,3,4,5,6,7',
            'reason_for_cancel' => 'nullable|required_if:status,6,7|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.show', $id)
                ->withErrors($validator);
        }

        $updateData = [
            'status' => $request->status
        ];

        if (in_array($request->status, [6, 7]) && $request->has('reason_for_cancel')) {
            $updateData['reason_for_cancel'] = $request->reason_for_cancel;
        }

        $order->update($updateData);

        return redirect()
            ->route('orders.show', $id)
            ->with('success', __('messages.Order_Status_Updated'));
    }

    /**
     * Update the payment status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status_payment' => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.show', $id)
                ->withErrors($validator);
        }

        $order->update([
            'status_payment' => $request->status_payment
        ]);

        return redirect()
            ->route('orders.show', $id)
            ->with('success', __('messages.Payment_Status_Updated'));
    }

    /**
     * Show user orders.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userOrders($id)
    {
        $user = User::findOrFail($id);
        $orders = Order::with(['driver', 'service'])
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.orders.user_orders', compact('orders', 'user'));
    }

    /**
     * Show driver orders.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function driverOrders($id)
    {
        $driver = Driver::findOrFail($id);
        $orders = Order::with(['user', 'service'])
            ->where('driver_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.orders.driver_orders', compact('orders', 'driver'));
    }
}