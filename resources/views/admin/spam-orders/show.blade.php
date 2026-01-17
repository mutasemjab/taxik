@extends('layouts.admin')

@section('title', __('messages.Spam_Order_Details'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-trash-alt"></i> {{ __('messages.Spam_Order_Details') }} #{{ $spamOrder->id }}
        </h1>
        <div>
            <a href="{{ route('spam-orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_List') }}
            </a>
            <form action="{{ route('spam-orders.destroy', $spamOrder->id) }}" 
                  method="POST" class="d-inline" 
                  onsubmit="return confirm('{{ __('messages.Confirm_Delete') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> {{ __('messages.Delete_Permanently') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Order Status & Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-ban"></i> {{ __('messages.Cancellation_Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-danger">{{ $spamOrder->getCancellationTypeText() }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-clock"></i> 
                                {{ __('messages.Cancelled_At') }}: 
                                {{ $spamOrder->cancelled_at ? $spamOrder->cancelled_at->format('Y-m-d H:i:s') : 'N/A' }}
                            </p>
                            @if(isset($timeMetrics['time_to_cancel']))
                            <p class="text-muted mb-2">
                                <i class="fas fa-hourglass-half"></i> 
                                {{ __('messages.Time_Until_Cancel') }}: 
                                {{ $timeMetrics['time_to_cancel_formatted'] }}
                            </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-danger">
                                <h6 class="font-weight-bold">{{ __('messages.Cancellation_Reason') }}:</h6>
                                <p class="mb-0">{{ $spamOrder->reason_for_cancel ?? __('messages.No_Reason_Provided') }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.Order_Number') }}</th>
                                    <td><span class="badge badge-secondary">{{ $spamOrder->number }}</span></td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Service') }}</th>
                                    <td>
                                        @if($spamOrder->service)
                                            {{ $spamOrder->service->name_en }} ({{ $spamOrder->service->name_ar }})
                                        @else
                                            {{ __('messages.Not_Available') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Payment_Method') }}</th>
                                    <td>{{ $spamOrder->getPaymentMethodText() }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('messages.Payment_Status') }}</th>
                                    <td>
                                        <span class="badge badge-{{ $spamOrder->status_payment == 'paid' ? 'success' : 'warning' }}">
                                            {{ $spamOrder->getPaymentStatusText() }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marker-alt"></i> {{ __('messages.Location_Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-success">{{ __('messages.Pickup_Location') }}</h6>
                            <p class="mb-1">{{ $spamOrder->pick_name }}</p>
                            <small class="text-muted">
                                {{ __('messages.Coordinates') }}: {{ $spamOrder->pick_lat }}, {{ $spamOrder->pick_lng }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-danger">{{ __('messages.Dropoff_Location') }}</h6>
                            <p class="mb-1">{{ $spamOrder->drop_name ?? __('messages.Not_Set') }}</p>
                            @if($spamOrder->drop_lat && $spamOrder->drop_lng)
                                <small class="text-muted">
                                    {{ __('messages.Coordinates') }}: {{ $spamOrder->drop_lat }}, {{ $spamOrder->drop_lng }}
                                </small>
                            @endif
                        </div>
                    </div>
                    @if($spamOrder->drop_lat && $spamOrder->drop_lng)
                    <div class="mt-3">
                        <span class="badge badge-info">
                            {{ __('messages.Distance') }}: {{ $spamOrder->getDistance() }} KM
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Pricing Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-dollar-sign"></i> {{ __('messages.Pricing_Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">{{ __('messages.Original_Price') }}</h6>
                                    <h4 class="mb-0">{{ number_format($spamOrder->total_price_before_discount, 2) }} JD</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">{{ __('messages.Final_Price') }}</h6>
                                    <h4 class="mb-0 text-primary">{{ number_format($spamOrder->total_price_after_discount, 2) }} JD</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">{{ __('messages.Admin_Commission') }}</h6>
                                    <h4 class="mb-0">{{ number_format($spamOrder->commision_of_admin, 2) }} JD</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Tracking Statistics Card -->
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> {{ __('messages.Driver_Statistics') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-primary">{{ $stats['total_notified'] }}</h2>
                                <small class="text-muted">{{ __('messages.Total_Notified') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-danger">{{ $stats['total_rejected'] }}</h2>
                                <small class="text-muted">{{ __('messages.Rejected') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-warning">{{ $stats['no_response'] }}</h2>
                                <small class="text-muted">{{ __('messages.No_Response') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <h2 class="text-success">{{ $stats['assigned'] }}</h2>
                                <small class="text-muted">{{ __('messages.Assigned') }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> 
                        <strong>{{ __('messages.How_It_Works') }}:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>{{ __('messages.Total_Notified') }}</strong>: {{ __('messages.Drivers_sent_notification') }}</li>
                            <li><strong>{{ __('messages.Rejected') }}</strong>: {{ __('messages.Drivers_removed_from_firebase') }}</li>
                            <li><strong>{{ __('messages.No_Response') }}</strong>: {{ __('messages.Drivers_still_in_firebase') }}</li>
                            <li><strong>{{ __('messages.Assigned') }}</strong>: {{ __('messages.Driver_accepted_order') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- All Drivers Notified Card -->
            @if($driversNotified->count() > 0)
            <div class="card shadow mb-4 border-left-info">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-bell"></i> {{ __('messages.All_Drivers_Notified') }} ({{ $driversNotified->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle"></i> 
                        {{ __('messages.All_drivers_notified_about_order') }}
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('messages.Driver') }}</th>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <th>{{ __('messages.Distance') }}</th>
                                    <th>{{ __('messages.Search_Radius') }}</th>
                                    <th>{{ __('messages.Notified_At') }}</th>
                                    <th>{{ __('messages.Response') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($driversNotified as $notified)
                                <tr>
                                    <td>
                                        @if($notified->driver)
                                            <a href="{{ route('drivers.show', $notified->driver_id) }}">
                                                {{ $notified->driver->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $notified->driver->phone ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ number_format($notified->distance_km, 2) }} KM
                                        </span>
                                    </td>
                                    <td>{{ $notified->search_radius_km }} KM</td>
                                    <td>
                                        <small>{{ $notified->notified_at->format('Y-m-d H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @if($notified->driver_id == $spamOrder->driver_id)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> {{ __('messages.Assigned') }}
                                            </span>
                                        @elseif($driversRejected->contains('driver_id', $notified->driver_id))
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> {{ __('messages.Rejected') }}
                                            </span>
                                        @elseif($driversNoResponse->contains('driver_id', $notified->driver_id))
                                            <span class="badge badge-warning">
                                                <i class="fas fa-question"></i> {{ __('messages.No_Response') }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('messages.Unknown') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                {{ __('messages.No_Drivers_Were_Notified') }}
            </div>
            @endif

            <!-- Drivers Who Rejected -->
            @if($driversRejected->count() > 0)
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-user-times"></i> {{ __('messages.Drivers_Who_Rejected') }} ({{ $driversRejected->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle"></i> 
                        {{ __('messages.These_drivers_rejected_by_removing_firebase') }}
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('messages.Driver') }}</th>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <th>{{ __('messages.Distance') }}</th>
                                    <th>{{ __('messages.Notified_At') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($driversRejected as $rejected)
                                <tr>
                                    <td>
                                        @if($rejected->driver)
                                            <a href="{{ route('drivers.show', $rejected->driver_id) }}">
                                                {{ $rejected->driver->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $rejected->driver->phone ?? 'N/A' }}</td>
                                    <td>{{ number_format($rejected->distance_km, 2) }} KM</td>
                                    <td>{{ $rejected->notified_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Drivers Who Didn't Respond -->
            @if($driversNoResponse->count() > 0)
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-question-circle"></i> {{ __('messages.Drivers_No_Response') }} ({{ $driversNoResponse->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle"></i> 
                        {{ __('messages.These_drivers_still_in_firebase') }}
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('messages.Driver') }}</th>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <th>{{ __('messages.Distance') }}</th>
                                    <th>{{ __('messages.Notified_At') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($driversNoResponse as $noResponse)
                                <tr>
                                    <td>
                                        @if($noResponse->driver)
                                            <a href="{{ route('drivers.show', $noResponse->driver_id) }}">
                                                {{ $noResponse->driver->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $noResponse->driver->phone ?? 'N/A' }}</td>
                                    <td>{{ number_format($noResponse->distance_km, 2) }} KM</td>
                                    <td>{{ $noResponse->notified_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- User Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user"></i> {{ __('messages.User_Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($spamOrder->user)
                        <div class="text-center mb-3">
                            @if($spamOrder->user->photo)
                                <img src="{{ asset('assets/admin/uploads/' . $spamOrder->user->photo) }}" 
                                     alt="{{ $spamOrder->user->name }}" 
                                     class="img-profile rounded-circle mb-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" 
                                     alt="No Image" 
                                     class="img-profile rounded-circle mb-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @endif
                            <h5>{{ $spamOrder->user->name }}</h5>
                        </div>

                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('messages.Phone') }}
                                <span>{{ $spamOrder->user->phone }}</span>
                            </li>
                            @if($spamOrder->user->email)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ __('messages.Email') }}
                                    <span>{{ $spamOrder->user->email }}</span>
                                </li>
                            @endif
                        </ul>

                        <a href="{{ route('users.show', $spamOrder->user_id) }}" class="btn btn-info btn-block">
                            <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                        </a>
                    @else
                        <div class="alert alert-warning">
                            {{ __('messages.User_Not_Available') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Driver Information (if assigned) -->
            @if($spamOrder->driver_id)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-car"></i> {{ __('messages.Driver_Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($spamOrder->driver)
                        <div class="text-center mb-3">
                            @if($spamOrder->driver->photo)
                                <img src="{{ asset('assets/admin/uploads/' . $spamOrder->driver->photo) }}" 
                                     alt="{{ $spamOrder->driver->name }}" 
                                     class="img-profile rounded-circle mb-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" 
                                     alt="No Image" 
                                     class="img-profile rounded-circle mb-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @endif
                            <h5>{{ $spamOrder->driver->name }}</h5>
                        </div>

                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('messages.Phone') }}
                                <span>{{ $spamOrder->driver->phone }}</span>
                            </li>
                        </ul>

                        <a href="{{ route('drivers.show', $spamOrder->driver_id) }}" class="btn btn-info btn-block">
                            <i class="fas fa-car"></i> {{ __('messages.View_Profile') }}
                        </a>
                    @else
                        <div class="alert alert-warning">
                            {{ __('messages.Driver_Not_Available') }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- User Cancellation History -->
            @if($userCancellationHistory->count() > 0)
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-history"></i> {{ __('messages.User_Cancellation_History') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-exclamation-triangle"></i> 
                        {{ __('messages.This_user_cancelled') }} {{ $userCancellationHistory->count() + 1 }} {{ __('messages.orders') }}
                    </p>
                    <div class="list-group">
                        @foreach($userCancellationHistory->take(5) as $history)
                        <a href="{{ route('spam-orders.show', $history->id) }}" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">#{{ $history->number }}</h6>
                                <small>{{ $history->cancelled_at->format('Y-m-d') }}</small>
                            </div>
                            <p class="mb-1 text-muted small">
                                {{ Str::limit($history->reason_for_cancel, 50) }}
                            </p>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection