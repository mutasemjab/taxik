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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['user', 'driver', 'service', 'coupon'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'driver', 'service', 'coupon'])->findOrFail($id);
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
            
            'trip_started_at' => 'nullable|date',
            'trip_completed_at' => 'nullable|date|after_or_equal:trip_started_at',
            'actual_trip_duration_minutes' => 'nullable|numeric|min:0',
            'live_distance' => 'nullable|numeric|min:0',
            'returned_amount' => 'nullable|numeric|min:0',
            
            'status' => ['required', Rule::in([
                'pending', 'accepted', 'on_the_way', 'started', 
                'waiting_payment', 'completed', 'user_cancel_order', 
                'driver_cancel_order', 'arrived', 'cancel_cron_job'
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
        $order->update($data);

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
     * Filter orders by various criteria.
     */
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'service_id' => 'nullable|exists:services,id',
            'status' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'status_payment' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.index')
                ->withErrors($validator);
        }

        $query = Order::with(['user', 'driver', 'service', 'coupon']);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->payment_method && $request->payment_method != 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->status_payment && $request->status_payment != 'all') {
            $query->where('status_payment', $request->status_payment);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        $users = User::all();
        $drivers = Driver::all();
        $services = Service::all();

        return view('admin.orders.index', compact('orders', 'users', 'drivers', 'services'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in([
                'pending', 'accepted', 'on_the_way', 'started', 
                'waiting_payment', 'completed', 'user_cancel_order', 
                'driver_cancel_order', 'arrived', 'cancel_cron_job'
            ])],
            'reason_for_cancel' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('orders.show', $id)
                ->withErrors($validator);
        }

        $updateData = ['status' => $request->status];

        if (in_array($request->status, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']) 
            && $request->has('reason_for_cancel')) {
            $updateData['reason_for_cancel'] = $request->reason_for_cancel;
        }

        $order->update($updateData);

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

        return redirect()
            ->route('orders.show', $id)
            ->with('success', __('messages.Payment_Status_Updated'));
    }
}