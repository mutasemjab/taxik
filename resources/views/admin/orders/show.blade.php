@extends('layouts.admin')

@section('title', __('messages.View_Order'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            {{ __('messages.View_Order') }} #{{ $order->id }}
            @if($order->number)
                <small class="text-muted">({{ $order->number }})</small>
            @endif
        </h1>
        <div>
            <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Order Status Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Order_Status') }}</h6>
            <div>
                @php
                    $statusValue = is_object($order->status) ? $order->status->value : $order->status;
                @endphp
                <span class="badge badge-lg px-3 py-2
                    @if($statusValue == 'completed') badge-success
                    @elseif(in_array($statusValue, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'])) badge-danger
                    @elseif($statusValue == 'waiting_payment') badge-warning
                    @else badge-info
                    @endif">
                    {{ __(ucfirst(str_replace('_', ' ', $statusValue))) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            @php
                // Extract enum values at the top for reuse
                $statusValue = is_object($order->status) ? $order->status->value : $order->status;
                $paymentMethod = is_object($order->payment_method) ? $order->payment_method->value : $order->payment_method;
                $paymentStatus = is_object($order->status_payment) ? $order->status_payment->value : $order->status_payment;
            @endphp
            <div class="row">
                <div class="col-md-8">
                    <!-- Status Timeline -->
                    <div class="timeline">
                        <div class="timeline-item {{ $statusValue == 'pending' ? 'active' : 'completed' }}">
                            <i class="fas fa-clock"></i> {{ __('messages.Pending') }}
                        </div>
                        <div class="timeline-item {{ in_array($statusValue, ['accepted', 'on_the_way', 'arrived', 'started', 'waiting_payment', 'completed']) ? 'completed' : '' }}">
                            <i class="fas fa-check"></i> {{ __('messages.Accepted') }}
                        </div>
                        <div class="timeline-item {{ in_array($statusValue, ['on_the_way', 'arrived', 'started', 'waiting_payment', 'completed']) ? 'completed' : '' }}">
                            <i class="fas fa-car"></i> {{ __('messages.On_The_Way') }}
                        </div>
                        <div class="timeline-item {{ in_array($statusValue, ['arrived', 'started', 'waiting_payment', 'completed']) ? 'completed' : '' }}">
                            <i class="fas fa-map-marker-alt"></i> {{ __('messages.Arrived') }}
                        </div>
                        <div class="timeline-item {{ in_array($statusValue, ['started', 'waiting_payment', 'completed']) ? 'completed' : '' }}">
                            <i class="fas fa-play"></i> {{ __('messages.Started') }}
                        </div>
                        <div class="timeline-item {{ in_array($statusValue, ['waiting_payment', 'completed']) ? 'completed' : '' }}">
                            <i class="fas fa-credit-card"></i> {{ __('messages.Waiting_Payment') }}
                        </div>
                        <div class="timeline-item {{ $statusValue == 'completed' ? 'completed' : '' }}">
                            <i class="fas fa-flag-checkered"></i> {{ __('messages.Completed') }}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <!-- Update Status Form -->
                    <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST" class="w-100">
                        @csrf
                        <div class="form-group">
                            <label for="status">{{ __('messages.Change_Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pending" {{ $statusValue == 'pending' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="accepted" {{ $statusValue == 'accepted' ? 'selected' : '' }}>{{ __('messages.Accepted') }}</option>
                                <option value="on_the_way" {{ $statusValue == 'on_the_way' ? 'selected' : '' }}>{{ __('messages.On_The_Way') }}</option>
                                <option value="arrived" {{ $statusValue == 'arrived' ? 'selected' : '' }}>{{ __('messages.Arrived') }}</option>
                                <option value="started" {{ $statusValue == 'started' ? 'selected' : '' }}>{{ __('messages.Started') }}</option>
                                <option value="waiting_payment" {{ $statusValue == 'waiting_payment' ? 'selected' : '' }}>{{ __('messages.Waiting_Payment') }}</option>
                                <option value="completed" {{ $statusValue == 'completed' ? 'selected' : '' }}>{{ __('messages.Completed') }}</option>
                                <option value="user_cancel_order" {{ $statusValue == 'user_cancel_order' ? 'selected' : '' }}>{{ __('messages.User_Cancelled') }}</option>
                                <option value="driver_cancel_order" {{ $statusValue == 'driver_cancel_order' ? 'selected' : '' }}>{{ __('messages.Driver_Cancelled') }}</option>
                                <option value="cancel_cron_job" {{ $statusValue == 'cancel_cron_job' ? 'selected' : '' }}>{{ __('messages.Cancelled_Auto') }}</option>
                            </select>
                        </div>
                        <div class="form-group cancel-reason-container" 
                             style="display: {{ in_array($statusValue, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']) ? 'block' : 'none' }};">
                            <label for="reason_for_cancel">{{ __('messages.Cancellation_Reason') }}</label>
                            <textarea class="form-control" id="reason_for_cancel" name="reason_for_cancel" rows="2">{{ $order->reason_for_cancel }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> {{ __('messages.Update_Status') }}
                        </button>
                    </form>
                </div>
            </div>

            @if(in_array($statusValue, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']) && $order->reason_for_cancel)
            <div class="alert alert-danger mt-3">
                <strong>{{ __('messages.Cancellation_Reason') }}:</strong> {{ $order->reason_for_cancel }}
            </div>
            @endif
        </div>
    </div>

    <!-- Trip Tracking Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-route"></i> {{ __('messages.Trip_Tracking') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-light mb-3">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">{{ __('messages.Estimated_Time') }}</h6>
                            <h4 class="mb-0">{{ $order->estimated_time ?? 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light mb-3">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">{{ __('messages.Live_Distance') }}</h6>
                            <h4 class="mb-0">{{ number_format($order->live_distance, 2) }} {{ __('messages.KM') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light mb-3">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">{{ __('messages.Actual_Duration') }}</h6>
                            <h4 class="mb-0">{{ $order->actual_trip_duration_minutes ?? 'N/A' }} {{ __('messages.Minutes') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light mb-3">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">{{ __('messages.Returned_Amount') }}</h6>
                            <h4 class="mb-0 text-success">{{ number_format($order->returned_amount ?? 0, 2) }} {{ __('messages.JD') }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="30%">{{ __('messages.Trip_Started_At') }}</th>
                            <td>{{ $order->trip_started_at ? $order->trip_started_at->format('Y-m-d H:i:s') : __('messages.Not_Started') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.Trip_Completed_At') }}</th>
                            <td>{{ $order->trip_completed_at ? $order->trip_completed_at->format('Y-m-d H:i:s') : __('messages.Not_Completed') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Waiting Charges Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-clock"></i> {{ __('messages.Waiting_Charges_Details') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Pre-Trip Waiting -->
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-user-clock"></i> {{ __('messages.Pre_Trip_Waiting') }}
                    </h6>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="50%">{{ __('messages.Arrived_At') }}</th>
                                    <td>
                                        @if($order->arrived_at)
                                            {{ $order->arrived_at->format('Y-m-d H:i:s') }}
                                        @else
                                            <span class="text-muted">{{ __('messages.Not_Set') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Total_Waiting_Minutes') }}</th>
                                    <td>
                                        <span class="badge badge-info px-3 py-2">
                                            {{ $order->total_waiting_minutes }} {{ __('messages.Minutes') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Waiting_Charges') }}</th>
                                    <td>
                                        <span class="badge badge-success px-3 py-2">
                                            {{ number_format($order->waiting_charges, 2) }} {{ __('messages.JD') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- In-Trip Waiting -->
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-warning mb-3">
                        <i class="fas fa-traffic-light"></i> {{ __('messages.In_Trip_Waiting') }}
                    </h6>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="50%">{{ __('messages.In_Trip_Waiting_Minutes') }}</th>
                                    <td>
                                        <span class="badge badge-warning px-3 py-2">
                                            {{ $order->in_trip_waiting_minutes }} {{ __('messages.Minutes') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.In_Trip_Waiting_Charges') }}</th>
                                    <td>
                                        <span class="badge badge-success px-3 py-2">
                                            {{ number_format($order->in_trip_waiting_charges, 2) }} {{ __('messages.JD') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Total_Waiting_Charges') }}</th>
                                    <td>
                                        <span class="badge badge-primary px-3 py-2">
                                            {{ number_format($order->waiting_charges + $order->in_trip_waiting_charges, 2) }} {{ __('messages.JD') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Order_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">{{ __('messages.Pickup_Location') }}</h5>
                            <p>{{ $order->pick_name }}</p>
                            <small class="text-muted">{{ __('messages.Coordinates') }}: {{ $order->pick_lat }}, {{ $order->pick_lng }}</small>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">{{ __('messages.Dropoff_Location') }}</h5>
                            <p>{{ $order->drop_name ?? __('messages.Not_Set') }}</p>
                            @if($order->drop_lat && $order->drop_lng)
                            <small class="text-muted">{{ __('messages.Coordinates') }}: {{ $order->drop_lat }}, {{ $order->drop_lng }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $order->id }}</td>
                                </tr>
                                @if($order->number)
                                <tr>
                                    <th>{{ __('messages.Order_Number') }}</th>
                                    <td><span class="badge badge-primary">{{ $order->number }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>{{ __('messages.Service') }}</th>
                                    <td>
                                        @if($order->service)
                                        <a href="{{ route('services.show', $order->service_id) }}">
                                            {{ $order->service->name_en }} ({{ $order->service->name_ar }})
                                        </a>
                                        @else
                                        {{ __('messages.Not_Available') }}
                                        @endif
                                    </td>
                                </tr>
                                @if($order->coupon)
                                <tr>
                                    <th>{{ __('messages.Coupon') }}</th>
                                    <td>
                                        <span class="badge badge-success">{{ $order->coupon->code }}</span>
                                        ({{ $order->coupon->discount }}% {{ __('messages.Discount') }})
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Updated_At') }}</th>
                                    <td>{{ $order->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pricing Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Pricing_Details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <h6 class="card-title mb-0">{{ __('messages.Original_Price') }}</h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <h6 class="mb-0">{{ number_format($order->total_price_before_discount, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($order->discount_value > 0)
                            <div class="card mb-3 bg-light">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <h6 class="card-title mb-0 text-success">{{ __('messages.Discount') }}</h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <h6 class="mb-0 text-success">-{{ number_format($order->discount_value, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="card mb-3 bg-primary text-white">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <h6 class="card-title mb-0">{{ __('messages.Final_Price') }}</h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <h6 class="mb-0">{{ number_format($order->total_price_after_discount, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <h6 class="card-title mb-0">{{ __('messages.Driver_Earning') }}</h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <h6 class="mb-0">{{ number_format($order->net_price_for_driver, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <h6 class="card-title mb-0">{{ __('messages.Admin_Commission') }}</h6>
                                        </div>
                                        <div class="col-4 text-right">
                                            <h6 class="mb-0">{{ number_format($order->commision_of_admin, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>{{ __('messages.Payment_Method') }}</strong>
                                            <div class="mt-1">
                                                <span class="badge badge-primary px-3 py-2">
                                                    {{ __(ucfirst($paymentMethod)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <strong>{{ __('messages.Payment_Status') }}</strong>
                                            <div class="mt-1">
                                                <span class="badge px-3 py-2 {{ $paymentStatus == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                                    {{ __(ucfirst($paymentStatus)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <form action="{{ route('orders.updatePaymentStatus', $order->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="status_payment">{{ __('messages.Update_Payment_Status') }}</label>
                                <select class="form-control" id="status_payment" name="status_payment">
                                    <option value="pending" {{ $paymentStatus == 'pending' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                    <option value="paid" {{ $paymentStatus == 'paid' ? 'selected' : '' }}>{{ __('messages.Paid') }}</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('messages.Update_Payment_Status') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- User Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.User_Information') }}</h6>
                </div>
                <div class="card-body">
                    @if($order->user)
                    <div class="text-center mb-3">
                        @if($order->user->photo)
                        <img src="{{ asset('assets/admin/uploads/' . $order->user->photo) }}" alt="{{ $order->user->name }}" 
                             class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                        <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" 
                             class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                        <h5>{{ $order->user->name }}</h5>
                    </div>
                    
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('messages.Phone') }}
                            <span>{{ $order->user->phone }}</span>
                        </li>
                        @if($order->user->email)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('messages.Email') }}
                            <span>{{ $order->user->email }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('messages.Wallet_Balance') }}
                            <span class="badge badge-primary px-3 py-2">{{ number_format($order->user->balance, 2) }}</span>
                        </li>
                    </ul>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.show', $order->user_id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                        </a>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        {{ __('messages.User_Not_Available') }}
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Driver Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('messages.Driver_Information') }}</h6>
                </div>
                <div class="card-body">
                    @if($order->driver)
                    <div class="text-center mb-3">
                        @if($order->driver->photo)
                        <img src="{{ asset('assets/admin/uploads/' . $order->driver->photo) }}" alt="{{ $order->driver->name }}" 
                             class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                        <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" 
                             class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                        <h5>{{ $order->driver->name }}</h5>
                    </div>
                    
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('messages.Phone') }}
                            <span>{{ $order->driver->phone }}</span>
                        </li>
                        @if($order->driver->email)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('messages.Email') }}
                            <span>{{ $order->driver->email }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __('messages.Wallet_Balance') }}
                            <span class="badge badge-primary px-3 py-2">{{ number_format($order->driver->balance, 2) }}</span>
                        </li>
                    </ul>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('drivers.show', $order->driver_id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                        </a>
                    </div>
                    @else
                    <div class="alert alert-info">
                        {{ __('messages.No_Driver_Assigned') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#status').on('change', function() {
            var status = $(this).val();
            if (['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'].includes(status)) {
                $('.cancel-reason-container').show();
            } else {
                $('.cancel-reason-container').hide();
            }
        });
    });
</script>
@endsection