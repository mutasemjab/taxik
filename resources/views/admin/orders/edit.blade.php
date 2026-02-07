@extends('layouts.admin')

@section('title', __('messages.Edit_Order'))

@section('content')
    @php
        $statusValue = is_object($order->status) ? $order->status->value : $order->status;
        $paymentMethod = is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method;
        $paymentStatus = is_object($order->status_payment) ? $order->status_payment->value : $order->status_payment;
    @endphp
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('messages.Edit_Order') }} #{{ $order->id }}</h1>
            <div>
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> {{ __('messages.View') }}
                </a>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Order_Details') }}</h6>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Basic Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">{{ __('messages.Basic_Information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="number">{{ __('messages.Order_Number') }}</label>
                                        <input type="text" class="form-control" id="number" name="number"
                                            value="{{ old('number', $order->number) }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="user_id">{{ __('messages.User') }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="user_id" name="user_id" required>
                                            <option value="">{{ __('messages.Select_User') }}</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ old('user_id', $order->user_id) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="driver_id">{{ __('messages.Driver') }}</label>
                                        <select class="form-control" id="driver_id" name="driver_id">
                                            <option value="">{{ __('messages.Select_Driver') }}</option>
                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->id }}"
                                                    {{ old('driver_id', $order->driver_id) == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} ({{ $driver->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="service_id">{{ __('messages.Service') }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="service_id" name="service_id" required>
                                            <option value="">{{ __('messages.Select_Service') }}</option>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}"
                                                    {{ old('service_id', $order->service_id) == $service->id ? 'selected' : '' }}>
                                                    {{ $service->name_en }} ({{ $service->name_ar }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="coupon_id">{{ __('messages.Coupon') }}</label>
                                        <select class="form-control" id="coupon_id" name="coupon_id">
                                            <option value="">{{ __('messages.No_Coupon') }}</option>
                                            @foreach ($coupons as $coupon)
                                                <option value="{{ $coupon->id }}"
                                                    {{ old('coupon_id', $order->coupon_id) == $coupon->id ? 'selected' : '' }}>
                                                    {{ $coupon->code }} ({{ $coupon->discount }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="estimated_time">{{ __('messages.Estimated_Time') }}</label>
                                        <input type="text" class="form-control" id="estimated_time" name="estimated_time"
                                            value="{{ old('estimated_time', $order->estimated_time) }}"
                                            placeholder="e.g., 15 mins">
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Payment -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">{{ __('messages.Status_Payment') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="status">{{ __('messages.Status') }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="pending"
                                                {{ old('status', $statusValue) == 'pending' ? 'selected' : '' }}>
                                                {{ __('messages.Pending') }}</option>
                                            <option value="accepted"
                                                {{ old('status', $statusValue) == 'accepted' ? 'selected' : '' }}>
                                                {{ __('messages.Accepted') }}</option>
                                            <option value="on_the_way"
                                                {{ old('status', $statusValue) == 'on_the_way' ? 'selected' : '' }}>
                                                {{ __('messages.On_The_Way') }}</option>
                                            <option value="arrived"
                                                {{ old('status', $statusValue) == 'arrived' ? 'selected' : '' }}>
                                                {{ __('messages.Arrived') }}</option>
                                            <option value="started"
                                                {{ old('status', $statusValue) == 'started' ? 'selected' : '' }}>
                                                {{ __('messages.Started') }}</option>
                                            <option value="waiting_payment"
                                                {{ old('status', $statusValue) == 'waiting_payment' ? 'selected' : '' }}>
                                                {{ __('messages.Waiting_Payment') }}</option>
                                            <option value="completed"
                                                {{ old('status', $statusValue) == 'completed' ? 'selected' : '' }}>
                                                {{ __('messages.Completed') }}</option>
                                            <option value="user_cancel_order"
                                                {{ old('status', $statusValue) == 'user_cancel_order' ? 'selected' : '' }}>
                                                {{ __('messages.User_Cancelled') }}</option>
                                            <option value="driver_cancel_order"
                                                {{ old('status', $statusValue) == 'driver_cancel_order' ? 'selected' : '' }}>
                                                {{ __('messages.Driver_Cancelled') }}</option>
                                            <option value="cancel_cron_job"
                                                {{ old('status', $statusValue) == 'cancel_cron_job' ? 'selected' : '' }}>
                                                {{ __('messages.Cancelled_Auto') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group cancel-reason-container"
                                        style="display: {{ in_array($statusValue, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']) ? 'block' : 'none' }};">
                                        <label for="reason_for_cancel">{{ __('messages.Cancellation_Reason') }}</label>
                                        <textarea class="form-control" id="reason_for_cancel" name="reason_for_cancel" rows="2">{{ old('reason_for_cancel', $order->reason_for_cancel) }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_method">{{ __('messages.Payment_Method') }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="payment_method" name="payment_method" required>
                                            <option value="cash"
                                                {{ old('payment_method', $order->payment_method) == 'cash' ? 'selected' : '' }}>
                                                {{ __('messages.Cash') }}</option>
                                            <option value="visa"
                                                {{ old('payment_method', $order->payment_method) == 'visa' ? 'selected' : '' }}>
                                                {{ __('messages.Visa') }}</option>
                                            <option value="wallet"
                                                {{ old('payment_method', $order->payment_method) == 'wallet' ? 'selected' : '' }}>
                                                {{ __('messages.Wallet') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="status_payment">{{ __('messages.Payment_Status') }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" id="status_payment" name="status_payment" required>
                                            <option value="pending"
                                                {{ old('status_payment', $paymentStatus) == 'pending' ? 'selected' : '' }}>
                                                {{ __('messages.Pending') }}</option>
                                            <option value="paid"
                                                {{ old('status_payment', $paymentStatus) == 'paid' ? 'selected' : '' }}>
                                                {{ __('messages.Paid') }}</option>
                                        </select>
                                    </div>

                                    <!-- Hybrid Payment Section -->
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_hybrid_payment"
                                                name="is_hybrid_payment"
                                                {{ old('is_hybrid_payment', $order->is_hybrid_payment) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_hybrid_payment">
                                                {{ __('messages.Hybrid_Payment') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div id="hybrid-payment-fields"
                                        style="display: {{ old('is_hybrid_payment', $order->is_hybrid_payment) ? 'block' : 'none' }};">
                                        <div class="form-group">
                                            <label
                                                for="wallet_amount_used">{{ __('messages.Wallet_Amount_Used') }}</label>
                                            <input type="number" step="any" class="form-control"
                                                id="wallet_amount_used" name="wallet_amount_used"
                                                value="{{ old('wallet_amount_used', $order->wallet_amount_used) }}"
                                                min="0">
                                        </div>

                                        <div class="form-group">
                                            <label for="cash_amount_due">{{ __('messages.Cash_Amount_Due') }}</label>
                                            <input type="number" step="any" class="form-control"
                                                id="cash_amount_due" name="cash_amount_due"
                                                value="{{ old('cash_amount_due', $order->cash_amount_due) }}"
                                                min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trip Tracking -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold text-success">{{ __('messages.Trip_Tracking') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="trip_started_at">{{ __('messages.Trip_Started_At') }}</label>
                                        <input type="datetime-local" class="form-control" id="trip_started_at"
                                            name="trip_started_at"
                                            value="{{ old('trip_started_at', $order->trip_started_at ? $order->trip_started_at->format('Y-m-d\TH:i') : '') }}" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="trip_completed_at">{{ __('messages.Trip_Completed_At') }}</label>
                                        <input type="datetime-local" class="form-control" id="trip_completed_at"
                                            name="trip_completed_at"
                                            value="{{ old('trip_completed_at', $order->trip_completed_at ? $order->trip_completed_at->format('Y-m-d\TH:i') : '') }}" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            for="actual_trip_duration_minutes">{{ __('messages.Actual_Duration_Minutes') }}</label>
                                        <input type="number" step="any" class="form-control"
                                            id="actual_trip_duration_minutes" name="actual_trip_duration_minutes"
                                            value="{{ old('actual_trip_duration_minutes', $order->actual_trip_duration_minutes) }}"
                                            min="0" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="live_distance">{{ __('messages.Live_Distance_KM') }}</label>
                                        <input type="number" step="any" class="form-control" id="live_distance"
                                            name="live_distance"
                                            value="{{ old('live_distance', $order->live_distance) }}" min="0" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="returned_amount">{{ __('messages.Returned_Amount') }}</label>
                                        <input type="number" step="any" class="form-control" id="returned_amount"
                                            name="returned_amount"
                                            value="{{ old('returned_amount', $order->returned_amount) }}" min="0">
                                        <small
                                            class="form-text text-muted">{{ __('messages.Returned_Amount_Info') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Location Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">{{ __('messages.Pickup_Location') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="pick_name">{{ __('messages.Pickup_Name') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pick_name" name="pick_name"
                                            value="{{ old('pick_name', $order->pick_name) }}" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pick_lat">{{ __('messages.Latitude') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control" id="pick_lat"
                                                    name="pick_lat" value="{{ old('pick_lat', $order->pick_lat) }}"
                                                    required readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pick_lng">{{ __('messages.Longitude') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control" id="pick_lng"
                                                    name="pick_lng" value="{{ old('pick_lng', $order->pick_lng) }}"
                                                    required readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">{{ __('messages.Dropoff_Location') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="drop_name">{{ __('messages.Dropoff_Name') }}</label>
                                        <input type="text" class="form-control" id="drop_name" name="drop_name"
                                            value="{{ old('drop_name', $order->drop_name) }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="drop_lat">{{ __('messages.Latitude') }}</label>
                                                <input type="number" step="any" class="form-control" id="drop_lat"
                                                    name="drop_lat" value="{{ old('drop_lat', $order->drop_lat) }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="drop_lng">{{ __('messages.Longitude') }}</label>
                                                <input type="number" step="any" class="form-control" id="drop_lng"
                                                    name="drop_lng" value="{{ old('drop_lng', $order->drop_lng) }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold">{{ __('messages.Pricing_Details') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="total_price_before_discount">{{ __('messages.Original_Price') }}
                                                    <span class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control"
                                                    id="total_price_before_discount" name="total_price_before_discount"
                                                    value="{{ old('total_price_before_discount', $order->total_price_before_discount) }}"
                                                    required min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="discount_value">{{ __('messages.Discount') }}</label>
                                                <input type="number" step="any" class="form-control"
                                                    id="discount_value" name="discount_value"
                                                    value="{{ old('discount_value', $order->discount_value) }}"
                                                    min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="total_price_after_discount">{{ __('messages.Final_Price') }}
                                                    <span class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control"
                                                    id="total_price_after_discount" name="total_price_after_discount"
                                                    value="{{ old('total_price_after_discount', $order->total_price_after_discount) }}"
                                                    required min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="commision_of_admin">{{ __('messages.Admin_Commission') }}
                                                    <span class="text-danger">*</span></label>
                                                <input type="number" step="any" class="form-control"
                                                    id="commision_of_admin" name="commision_of_admin"
                                                    value="{{ old('commision_of_admin', $order->commision_of_admin) }}"
                                                    required min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="net_price_for_driver">{{ __('messages.Driver_Earning') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="any" class="form-control"
                                            id="net_price_for_driver" name="net_price_for_driver"
                                            value="{{ old('net_price_for_driver', $order->net_price_for_driver) }}"
                                            required min="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Waiting Charges Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="m-0 font-weight-bold text-info">
                                        <i class="fas fa-clock"></i> {{ __('messages.Waiting_Charges_Details') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="arrived_at">{{ __('messages.Arrived_At') }}</label>
                                                <input type="datetime-local" class="form-control" id="arrived_at"
                                                    name="arrived_at"
                                                    value="{{ old('arrived_at', $order->arrived_at ? $order->arrived_at->format('Y-m-d\TH:i') : '') }}" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label
                                                    for="total_waiting_minutes">{{ __('messages.Total_Waiting_Minutes') }}</label>
                                                <input type="number" class="form-control" id="total_waiting_minutes"
                                                    name="total_waiting_minutes"
                                                    value="{{ old('total_waiting_minutes', $order->total_waiting_minutes) }}"
                                                    min="0" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="waiting_charges">{{ __('messages.Waiting_Charges') }}</label>
                                                <input type="number" step="any" class="form-control"
                                                    id="waiting_charges" name="waiting_charges"
                                                    value="{{ old('waiting_charges', $order->waiting_charges) }}"
                                                    min="0" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="in_trip_waiting_minutes">{{ __('messages.In_Trip_Waiting_Minutes') }}</label>
                                                <input type="number" class="form-control" id="in_trip_waiting_minutes"
                                                    name="in_trip_waiting_minutes"
                                                    value="{{ old('in_trip_waiting_minutes', $order->in_trip_waiting_minutes) }}"
                                                    min="0" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="in_trip_waiting_charges">{{ __('messages.In_Trip_Waiting_Charges') }}</label>
                                                <input type="number" step="any" class="form-control"
                                                    id="in_trip_waiting_charges" name="in_trip_waiting_charges"
                                                    value="{{ old('in_trip_waiting_charges', $order->in_trip_waiting_charges) }}"
                                                    min="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="map" style="height: 200px; width: 100%; margin-bottom: 20px;"></div>
                        </div>
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.Update') }}
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('messages.Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Show/hide cancellation reason field based on status
            $('#status').on('change', function() {
                var status = $(this).val();
                if (['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'].includes(status)) {
                    $('.cancel-reason-container').show();
                    $('#reason_for_cancel').prop('required', true);
                } else {
                    $('.cancel-reason-container').hide();
                    $('#reason_for_cancel').prop('required', false);
                }
            });

            // Show/hide hybrid payment fields
            $('#is_hybrid_payment').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#hybrid-payment-fields').slideDown();
                } else {
                    $('#hybrid-payment-fields').slideUp();
                    $('#wallet_amount_used').val(0);
                    $('#cash_amount_due').val(0);
                }
            });

            // Calculate final price when discount changes
            $('#discount_value').on('input', function() {
                calculatePrices();
            });

            $('#total_price_before_discount').on('input', function() {
                calculatePrices();
            });
        });

        function calculatePrices() {
            var totalBeforeDiscount = parseFloat($('#total_price_before_discount').val()) || 0;
            var discount = parseFloat($('#discount_value').val()) || 0;

            if (discount > totalBeforeDiscount) {
                alert("{{ __('messages.Discount_Too_High') }}");
                $('#discount_value').val(0);
                discount = 0;
            }

            var totalAfterDiscount = totalBeforeDiscount - discount;
            $('#total_price_after_discount').val(totalAfterDiscount.toFixed(2));
        }
    </script>
@endsection
