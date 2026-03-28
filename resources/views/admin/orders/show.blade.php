@extends('layouts.admin')

@section('title', __('messages.View_Order'))

@php
    $statusValue   = is_object($order->status)         ? $order->status->value         : $order->status;
    $paymentMethod = is_object($order->payment_method)  ? $order->payment_method->value  : $order->payment_method;
    $paymentStatus = is_object($order->status_payment)  ? $order->status_payment->value  : $order->status_payment;

    $isCompleted  = $statusValue === 'completed';
    $isCancelled  = in_array($statusValue, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']);

    $statusBadgeClass = $isCompleted  ? 'badge-success'
                      : ($isCancelled ? 'badge-danger'
                      : ($statusValue === 'waiting_payment' ? 'badge-warning' : 'badge-info'));

    // Build a map: status => timestamp from history
    $historyMap = $order->statusHistories->keyBy('status');

    // Ordered steps for normal flow
    $steps = [
        ['key' => 'pending',         'icon' => 'fa-clock',           'label' => __('messages.Pending')],
        ['key' => 'accepted',        'icon' => 'fa-check',           'label' => __('messages.Accepted')],
        ['key' => 'driver_accepted', 'icon' => 'fa-user-check',      'label' => __('messages.Driver_Accepted') ],
        ['key' => 'on_the_way',      'icon' => 'fa-car',             'label' => __('messages.On_The_Way')],
        ['key' => 'arrived',         'icon' => 'fa-map-marker-alt',  'label' => __('messages.Arrived')],
        ['key' => 'started',         'icon' => 'fa-play',            'label' => __('messages.Started')],
        ['key' => 'waiting_payment', 'icon' => 'fa-credit-card',     'label' => __('messages.Waiting_Payment')],
        ['key' => 'completed',       'icon' => 'fa-flag-checkered',  'label' => __('messages.Completed')],
    ];
@endphp

@section('content')
<div class="container-fluid">

    {{-- ===== Header ===== --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-route text-primary mr-2"></i>
                {{ __('messages.View_Order') }}
                <span class="badge badge-secondary ml-1">#{{ $order->id }}</span>
                @if($order->number)
                    <span class="badge badge-light border ml-1">{{ $order->number }}</span>
                @endif
            </h1>
            <small class="text-muted ml-1">
                <i class="fas fa-calendar-alt"></i>
                {{ $order->created_at->format('Y-m-d  H:i:s') }}
            </small>
        </div>
        <div>
            <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
        </div>
    </div>

    {{-- ===== Hybrid Payment Alert ===== --}}
    @if($order->is_hybrid_payment)
    <div class="alert alert-info d-flex align-items-center">
        <i class="fas fa-wallet fa-2x mr-3"></i>
        <div>
            <strong>{{ __('messages.Hybrid_Payment') }}</strong> &mdash;
            {{ __('messages.Wallet_Amount_Used') }}: <strong>JD {{ number_format($order->wallet_amount_used, 2) }}</strong>
            &nbsp;|&nbsp;
            {{ __('messages.Cash_Amount_Due') }}: <strong>JD {{ number_format($order->cash_amount_due, 2) }}</strong>
        </div>
    </div>
    @endif

    {{-- ===== Cancellation Reason Alert ===== --}}
    @if($isCancelled && $order->reason_for_cancel)
    <div class="alert alert-danger">
        <i class="fas fa-ban mr-1"></i>
        <strong>{{ __('messages.Cancellation_Reason') }}:</strong> {{ $order->reason_for_cancel }}
    </div>
    @endif

    {{-- ===== Row 1: Status Timeline + Change Status ===== --}}
    <div class="row">
        {{-- Status Timeline --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-1"></i> {{ __('messages.Status_History') }}
                    </h6>
                    <span class="badge {{ $statusBadgeClass }} px-3 py-2" style="font-size:0.9em;">
                        {{ __('messages.' . ucwords(str_replace('_', ' ', $statusValue))) }}
                    </span>
                </div>
                <div class="card-body">
                    {{-- Normal flow steps --}}
                    <div class="order-timeline">
                        @foreach($steps as $step)
                            @php
                                $hist   = $historyMap->get($step['key']) ?? null;
                                $isDone = $hist !== null;
                            @endphp
                            <div class="timeline-step {{ $isDone ? 'done' : 'pending-step' }}">
                                <div class="timeline-icon">
                                    <i class="fas {{ $step['icon'] }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">{{ $step['label'] }}</div>
                                    @if($isDone)
                                        <div class="timeline-time">
                                            <i class="fas fa-clock fa-xs"></i>
                                            {{ $hist->changed_at->format('Y-m-d  H:i:s') }}
                                        </div>
                                        @if($hist->changed_by_type && $hist->changed_by)
                                            <div class="timeline-by">
                                                <i class="fas fa-{{ $hist->changed_by_type === 'driver' ? 'car' : 'user' }} fa-xs"></i>
                                                {{ ucfirst($hist->changed_by_type) }} #{{ $hist->changed_by }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="timeline-time text-muted">—</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Cancelled statuses (only if applicable) --}}
                        @foreach(['user_cancel_order' => ['fa-user-times', __('messages.User_Cancelled')], 'driver_cancel_order' => ['fa-car-crash', __('messages.Driver_Cancelled')], 'cancel_cron_job' => ['fa-robot', __('messages.Cancelled_Auto')]] as $cancelKey => [$cancelIcon, $cancelLabel])
                            @if($historyMap->has($cancelKey))
                                @php $hist = $historyMap->get($cancelKey); @endphp
                                <div class="timeline-step cancelled">
                                    <div class="timeline-icon">
                                        <i class="fas {{ $cancelIcon }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">{{ $cancelLabel }}</div>
                                        <div class="timeline-time">
                                            <i class="fas fa-clock fa-xs"></i>
                                            {{ $hist->changed_at->format('Y-m-d  H:i:s') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Full history table if more than steps --}}
                    @if($order->statusHistories->count() > 0)
                    <div class="mt-4">
                        <h6 class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-list-alt mr-1"></i> {{ __('messages.Order_Status_History') }}
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.Status') }}</th>
                                        <th>{{ __('messages.Changed_At') }}</th>
                                        <th>{{ __('messages.Changed_By') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->statusHistories as $i => $h)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            @php
                                                $hVal = $h->status;
                                                $hClass = $hVal === 'completed' ? 'success'
                                                        : (in_array($hVal, ['user_cancel_order','driver_cancel_order','cancel_cron_job']) ? 'danger'
                                                        : ($hVal === 'waiting_payment' ? 'warning' : 'info'));
                                            @endphp
                                            <span class="badge badge-{{ $hClass }}">
                                                {{ __('messages.' . ucwords(str_replace('_', ' ', $hVal))) }}
                                            </span>
                                        </td>
                                        <td><small>{{ $h->changed_at->format('Y-m-d  H:i:s') }}</small></td>
                                        <td>
                                            @if($h->changed_by_type && $h->changed_by)
                                                <span class="badge badge-secondary">
                                                    {{ ucfirst($h->changed_by_type) }} #{{ $h->changed_by }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Change Status Form --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exchange-alt mr-1"></i> {{ __('messages.Change_Status') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="status">{{ __('messages.Order_Status') }}</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pending"           {{ $statusValue == 'pending'           ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="accepted"          {{ $statusValue == 'accepted'          ? 'selected' : '' }}>{{ __('messages.Accepted') }}</option>
                                <option value="on_the_way"        {{ $statusValue == 'on_the_way'        ? 'selected' : '' }}>{{ __('messages.On_The_Way') }}</option>
                                <option value="arrived"           {{ $statusValue == 'arrived'           ? 'selected' : '' }}>{{ __('messages.Arrived') }}</option>
                                <option value="started"           {{ $statusValue == 'started'           ? 'selected' : '' }}>{{ __('messages.Started') }}</option>
                                <option value="waiting_payment"   {{ $statusValue == 'waiting_payment'   ? 'selected' : '' }}>{{ __('messages.Waiting_Payment') }}</option>
                                <option value="completed"         {{ $statusValue == 'completed'         ? 'selected' : '' }}>{{ __('messages.Completed') }}</option>
                                <option value="user_cancel_order" {{ $statusValue == 'user_cancel_order' ? 'selected' : '' }}>{{ __('messages.User_Cancelled') }}</option>
                                <option value="driver_cancel_order" {{ $statusValue == 'driver_cancel_order' ? 'selected' : '' }}>{{ __('messages.Driver_Cancelled') }}</option>
                                <option value="cancel_cron_job"   {{ $statusValue == 'cancel_cron_job'   ? 'selected' : '' }}>{{ __('messages.Cancelled_Auto') }}</option>
                            </select>
                        </div>
                        <div class="form-group cancel-reason-container"
                             style="display: {{ $isCancelled ? 'block' : 'none' }};">
                            <label for="reason_for_cancel">{{ __('messages.Cancellation_Reason') }}</label>
                            <textarea class="form-control" id="reason_for_cancel" name="reason_for_cancel" rows="2">{{ $order->reason_for_cancel }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-save mr-1"></i> {{ __('messages.Update_Status') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Update Payment Status --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-money-bill-wave mr-1"></i> {{ __('messages.Update_Payment_Status') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('orders.updatePaymentStatus', $order->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="status_payment">{{ __('messages.Payment_Status') }}</label>
                            <select class="form-control" id="status_payment" name="status_payment">
                                <option value="pending" {{ $paymentStatus == 'pending' ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="paid"    {{ $paymentStatus == 'paid'    ? 'selected' : '' }}>{{ __('messages.Paid') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-save mr-1"></i> {{ __('messages.Update_Payment_Status') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Row 2: Order Info + Pricing + User + Driver ===== --}}
    <div class="row">

        {{-- Left: Order Details + Pricing --}}
        <div class="col-lg-8">

            {{-- Order Details --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i> {{ __('messages.Order_Details') }}
                    </h6>
                </div>
                <div class="card-body">

                    {{-- Locations --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <span class="mr-2 mt-1" style="color:#28a745;font-size:1.3em;">●</span>
                                <div>
                                    <div class="font-weight-bold">{{ __('messages.Pickup_Location') }}</div>
                                    <div>{{ $order->pick_name }}</div>
                                    @if($order->pick_lat && $order->pick_lng)
                                    <a href="https://www.google.com/maps?q={{ $order->pick_lat }},{{ $order->pick_lng }}"
                                       target="_blank" class="btn btn-outline-success btn-xs mt-1" style="font-size:0.75em;padding:2px 8px;">
                                        <i class="fas fa-map-marker-alt"></i> {{ __('messages.View_On_Map') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <span class="mr-2 mt-1" style="color:#dc3545;font-size:1.3em;">●</span>
                                <div>
                                    <div class="font-weight-bold">{{ __('messages.Dropoff_Location') }}</div>
                                    <div>{{ $order->drop_name ?? __('messages.Not_Set') }}</div>
                                    @if($order->drop_lat && $order->drop_lng)
                                    <a href="https://www.google.com/maps?q={{ $order->drop_lat }},{{ $order->drop_lng }}"
                                       target="_blank" class="btn btn-outline-danger btn-xs mt-1" style="font-size:0.75em;padding:2px 8px;">
                                        <i class="fas fa-map-marker-alt"></i> {{ __('messages.View_On_Map') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th width="35%">{{ __('messages.Service') }}</th>
                                    <td>
                                        @if($order->service)
                                            <a href="{{ route('services.show', $order->service_id) }}">
                                                {{ $order->service->name_en }} ({{ $order->service->name_ar }})
                                            </a>
                                        @else
                                            <span class="text-muted">{{ __('messages.Not_Available') }}</span>
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
                                    <th>{{ __('messages.Payment_Method') }}</th>
                                    <td>
                                        <span class="badge badge-primary">{{ __(ucfirst($paymentMethod)) }}</span>
                                        &nbsp;
                                        <span class="badge {{ $paymentStatus == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                            {{ __(ucfirst($paymentStatus)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Created_At') }}</th>
                                    <td>{{ $order->created_at->format('Y-m-d  H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Updated_At') }}</th>
                                    <td>{{ $order->updated_at->format('Y-m-d  H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pricing Details --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-coins mr-1"></i> {{ __('messages.Pricing_Details') }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <th width="50%">{{ __('messages.Original_Price') }}</th>
                                <td>{{ number_format($order->total_price_before_discount, 2) }} {{ __('messages.JD') }}</td>
                            </tr>
                            @if($order->discount_value > 0)
                            <tr class="table-success">
                                <th>{{ __('messages.Discount') }}</th>
                                <td class="text-success">- {{ number_format($order->discount_value, 2) }} {{ __('messages.JD') }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th>{{ __('messages.Final_Price') }}</th>
                                <td><strong>{{ number_format($order->total_price_after_discount, 2) }} {{ __('messages.JD') }}</strong></td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Driver_Earning') }}</th>
                                <td>{{ number_format($order->net_price_for_driver, 2) }} {{ __('messages.JD') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Admin_Commission') }}</th>
                                <td>{{ number_format($order->commision_of_admin, 2) }} {{ __('messages.JD') }}</td>
                            </tr>
                            @if($order->returned_amount > 0)
                            <tr class="table-warning">
                                <th>{{ __('messages.Returned_Amount') }}</th>
                                <td>{{ number_format($order->returned_amount, 2) }} {{ __('messages.JD') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Trip Tracking --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-route mr-1"></i> {{ __('messages.Trip_Tracking') }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr>
                                <th width="35%">{{ __('messages.Trip_Started_At') }}</th>
                                <td>
                                    {{ $order->trip_started_at
                                        ? $order->trip_started_at->format('Y-m-d  H:i:s')
                                        : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Trip_Completed_At') }}</th>
                                <td>
                                    {{ $order->trip_completed_at
                                        ? $order->trip_completed_at->format('Y-m-d  H:i:s')
                                        : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Estimated_Time') }}</th>
                                <td>{{ $order->estimated_time ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Live_Distance') }}</th>
                                <td>{{ number_format($order->live_distance, 2) }} {{ __('messages.KM') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Actual_Duration') }}</th>
                                <td>{{ $order->actual_trip_duration_minutes ? $order->actual_trip_duration_minutes . ' ' . __('messages.Minutes') : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Waiting Charges --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-hourglass-half mr-1"></i> {{ __('messages.Waiting_Charges_Details') }}
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th></th>
                                <th>{{ __('messages.Pre_Trip_Waiting') }}</th>
                                <th>{{ __('messages.In_Trip_Waiting') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>{{ __('messages.Arrived_At') }}</th>
                                <td>{{ $order->arrived_at ? $order->arrived_at->format('Y-m-d  H:i:s') : '—' }}</td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Total_Waiting_Minutes') }}</th>
                                <td><span class="badge badge-info">{{ $order->total_waiting_minutes }} {{ __('messages.Minutes') }}</span></td>
                                <td><span class="badge badge-warning">{{ $order->in_trip_waiting_minutes }} {{ __('messages.Minutes') }}</span></td>
                            </tr>
                            <tr>
                                <th>{{ __('messages.Waiting_Charges') }}</th>
                                <td><span class="badge badge-success">{{ number_format($order->waiting_charges, 2) }} {{ __('messages.JD') }}</span></td>
                                <td><span class="badge badge-success">{{ number_format($order->in_trip_waiting_charges, 2) }} {{ __('messages.JD') }}</span></td>
                            </tr>
                            <tr class="table-light">
                                <th>{{ __('messages.Total_Waiting_Charges') }}</th>
                                <td colspan="2">
                                    <span class="badge badge-primary">
                                        {{ number_format($order->waiting_charges + $order->in_trip_waiting_charges, 2) }} {{ __('messages.JD') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: User + Driver + Rating + Complaints --}}
        <div class="col-lg-4">

            {{-- User Information --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user mr-1"></i> {{ __('messages.User_Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($order->user)
                        <div class="text-center mb-3">
                            <img src="{{ $order->user->photo ? asset('assets/admin/uploads/' . $order->user->photo) : asset('assets/admin/img/undraw_profile.svg') }}"
                                 class="img-profile rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                            <h6 class="mt-2 mb-0 font-weight-bold">{{ $order->user->name }}</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">{{ __('messages.Phone') }}</span>
                                <span>{{ $order->user->phone }}</span>
                            </li>
                            @if($order->user->email)
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">{{ __('messages.Email') }}</span>
                                <span>{{ $order->user->email }}</span>
                            </li>
                            @endif
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">{{ __('messages.Wallet_Balance') }}</span>
                                <span class="badge badge-primary">{{ number_format($order->user->balance, 2) }} JD</span>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('users.show', $order->user_id) }}" class="btn btn-info btn-sm btn-block">
                                <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">{{ __('messages.User_Not_Available') }}</div>
                    @endif
                </div>
            </div>

            {{-- Driver Information --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-car mr-1"></i> {{ __('messages.Driver_Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($order->driver)
                        <div class="text-center mb-3">
                            <img src="{{ $order->driver->photo ? asset('assets/admin/uploads/' . $order->driver->photo) : asset('assets/admin/img/undraw_profile.svg') }}"
                                 class="img-profile rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                            <h6 class="mt-2 mb-0 font-weight-bold">{{ $order->driver->name }}</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">{{ __('messages.Phone') }}</span>
                                <span>{{ $order->driver->phone }}</span>
                            </li>
                            @if($order->driver->email)
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">{{ __('messages.Email') }}</span>
                                <span>{{ $order->driver->email }}</span>
                            </li>
                            @endif
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted">{{ __('messages.Wallet_Balance') }}</span>
                                <span class="badge badge-primary">{{ number_format($order->driver->balance, 2) }} JD</span>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('drivers.show', $order->driver_id) }}" class="btn btn-info btn-sm btn-block">
                                <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">{{ __('messages.No_Driver_Assigned') }}</div>
                    @endif
                </div>
            </div>

            {{-- Rating --}}
            @if($order->rating)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-star mr-1"></i> {{ __('messages.Rating') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $order->rating->rating ? 'text-warning' : 'text-muted' }}"></i>
                        @endfor
                        <strong class="ml-1">{{ $order->rating->rating }}/5</strong>
                    </div>
                    <p class="text-muted mb-1">{{ $order->rating->review ?? __('messages.No_Review') }}</p>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> {{ $order->rating->created_at->format('Y-m-d  H:i:s') }}
                    </small>
                </div>
            </div>
            @endif

            {{-- Complaints --}}
            @if($order->complaints->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        {{ __('messages.Complaints') }}
                        <span class="badge badge-danger ml-1">{{ $order->complaints->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-2">
                    @foreach($order->complaints as $complaint)
                    @php
                        $cBadge = [1 => 'warning', 2 => 'info', 3 => 'success'][$complaint->status] ?? 'secondary';
                        $cLabel = [1 => __('messages.Pending'), 2 => __('messages.In_Progress'), 3 => __('messages.Resolved')][$complaint->status] ?? __('messages.Unknown');
                    @endphp
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong style="font-size:0.9em;">{{ $complaint->subject }}</strong>
                            <span class="badge badge-{{ $cBadge }} ml-1">{{ $cLabel }}</span>
                        </div>
                        <p class="text-muted mb-1" style="font-size:0.85em;">{{ $complaint->description }}</p>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> {{ $complaint->created_at->format('Y-m-d  H:i:s') }}
                            @if($complaint->user_id)
                                &nbsp;|&nbsp;<i class="fas fa-user"></i> {{ $complaint->user->name ?? '' }}
                            @elseif($complaint->driver_id)
                                &nbsp;|&nbsp;<i class="fas fa-car"></i> {{ $complaint->driver->name ?? '' }}
                            @endif
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<style>
/* ====== Order Timeline ====== */
.order-timeline {
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-left: 16px;
}
.order-timeline::before {
    content: '';
    position: absolute;
    left: 28px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 10px 0;
    position: relative;
}
.timeline-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
    background: #e9ecef;
    color: #aaa;
    border: 2px solid #dee2e6;
}
.timeline-step.done .timeline-icon {
    background: #1cc88a;
    color: #fff;
    border-color: #1cc88a;
}
.timeline-step.cancelled .timeline-icon {
    background: #e74a3b;
    color: #fff;
    border-color: #e74a3b;
}
.timeline-content {
    padding-top: 4px;
}
.timeline-label {
    font-weight: 600;
    font-size: 0.9em;
    color: #333;
}
.timeline-step.pending-step .timeline-label {
    color: #aaa;
}
.timeline-time {
    font-size: 0.8em;
    color: #555;
    margin-top: 2px;
}
.timeline-by {
    font-size: 0.75em;
    color: #888;
}
</style>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('#status').on('change', function () {
        var s = $(this).val();
        if (['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'].includes(s)) {
            $('.cancel-reason-container').show();
        } else {
            $('.cancel-reason-container').hide();
        }
    });
});
</script>
@endsection
