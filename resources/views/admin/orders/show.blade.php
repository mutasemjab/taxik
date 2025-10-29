@extends('layouts.admin')

@section('title', __('messages.View_Order'))

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('messages.View_Order') }} #{{ $order->id }}</h1>
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
                <span class="badge badge-{{ $order->getStatusClass() }} px-3 py-2">
                    {{ $order->getStatusText() }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <!-- Status Progress -->
                    <div class="position-relative mb-4">
                        <div class="progress" style="height: 3px;">
                            @if($order->status >= 1 && $order->status <= 5)
                                @php
                                    $progressPercentage = ($order->status - 1) / 4 * 100;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercentage }}%" 
                                     aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            @elseif($order->status == 6 || $order->status == 7)
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 100%" 
                                     aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="text-center" style="width: 20%;">
                                <div class="rounded-circle {{ $order->status >= 1 ? 'bg-success' : 'bg-secondary' }} text-white d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px; position: relative; top: -15px;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="small">{{ __('messages.Pending') }}</div>
                            </div>
                            <div class="text-center" style="width: 20%;">
                                <div class="rounded-circle {{ $order->status >= 2 ? 'bg-success' : 'bg-secondary' }} text-white d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px; position: relative; top: -15px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="small">{{ __('messages.Accepted') }}</div>
                            </div>
                            <div class="text-center" style="width: 20%;">
                                <div class="rounded-circle {{ $order->status >= 3 ? 'bg-success' : 'bg-secondary' }} text-white d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px; position: relative; top: -15px;">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="small">{{ __('messages.On_Way') }}</div>
                            </div>
                            <div class="text-center" style="width: 20%;">
                                <div class="rounded-circle {{ $order->status >= 4 ? 'bg-success' : 'bg-secondary' }} text-white d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px; position: relative; top: -15px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="small">{{ __('messages.In_Progress') }}</div>
                            </div>
                            <div class="text-center" style="width: 20%;">
                                <div class="rounded-circle {{ $order->status >= 5 ? 'bg-success' : 'bg-secondary' }} text-white d-inline-flex justify-content-center align-items-center" style="width: 30px; height: 30px; position: relative; top: -15px;">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                                <div class="small">{{ __('messages.Delivered') }}</div>
                            </div>
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
                                <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>{{ __('messages.Driver_Accepted') }}</option>
                                <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>{{ __('messages.Driver_Going_To_User') }}</option>
                                <option value="4" {{ $order->status == 4 ? 'selected' : '' }}>{{ __('messages.User_With_Driver') }}</option>
                                <option value="5" {{ $order->status == 5 ? 'selected' : '' }}>{{ __('messages.Delivered') }}</option>
                                <option value="6" {{ $order->status == 6 ? 'selected' : '' }}>{{ __('messages.User_Cancelled') }}</option>
                                <option value="7" {{ $order->status == 7 ? 'selected' : '' }}>{{ __('messages.Driver_Cancelled') }}</option>
                            </select>
                        </div>
                        <div class="form-group cancel-reason-container" style="display: {{ in_array($order->status, [6, 7]) ? 'block' : 'none' }};">
                            <label for="reason_for_cancel">{{ __('messages.Cancellation_Reason') }}</label>
                            <textarea class="form-control" id="reason_for_cancel" name="reason_for_cancel" rows="2">{{ $order->reason_for_cancel }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> {{ __('messages.Update_Status') }}
                        </button>
                    </form>
                </div>
            </div>

            @if($order->isCancelled() && $order->reason_for_cancel)
            <div class="alert alert-danger mt-3">
                <strong>{{ __('messages.Cancellation_Reason') }}:</strong> {{ $order->reason_for_cancel }}
            </div>
            @endif
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
                            <p>{{ $order->drop_name }}</p>
                            <small class="text-muted">{{ __('messages.Coordinates') }}: {{ $order->drop_lat }}, {{ $order->drop_lng }}</small>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold">{{ __('messages.Route_Information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <h6>{{ __('messages.Distance') }}</h6>
                                    <h3 class="text-primary">{{ $order->getDistance() }} {{ __('messages.KM') }}</h3>
                                </div>
                                <div class="col-md-8">
                                    <div id="map" style="height: 200px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">{{ __('messages.ID') }}</th>
                                    <td>{{ $order->id }}</td>
                                </tr>
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
                                            <h6 class="mb-0">{{ $order->total_price_before_discount }}</h6>
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
                                            <h6 class="mb-0 text-success">-{{ $order->discount_value }}</h6>
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
                                            <h6 class="mb-0">{{ $order->total_price_after_discount }}</h6>
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
                                            <h6 class="mb-0">{{ $order->net_price_for_driver }}</h6>
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
                                            <h6 class="mb-0">{{ $order->commision_of_admin }}</h6>
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
                                                    {{ $order->getPaymentMethodText() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <strong>{{ __('messages.Payment_Status') }}</strong>
                                            <div class="mt-1">
                                                <span class="badge badge-{{ $order->getPaymentStatusClass() }} px-3 py-2">
                                                    {{ $order->getPaymentStatusText() }}
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
                                    <option value="1" {{ $order->status_payment == 1 ? 'selected' : '' }}>{{ __('messages.Pending') }}</option>
                                    <option value="2" {{ $order->status_payment == 2 ? 'selected' : '' }}>{{ __('messages.Paid') }}</option>
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
                        <img src="{{ asset('assets/admin/uploads/' . $order->user->photo) }}" alt="{{ $order->user->name }}" class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                        <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
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
                            <span class="badge badge-primary px-3 py-2">{{ $order->user->balance }}</span>
                        </li>
                    </ul>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.show', $order->user_id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                        </a>
                        <a href="{{ route('orders.userOrders', $order->user_id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> {{ __('messages.View_Orders') }}
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
                        <img src="{{ asset('assets/admin/uploads/' . $order->driver->photo) }}" alt="{{ $order->driver->name }}" class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                        <img src="{{ asset('assets/admin/img/undraw_profile.svg') }}" alt="No Image" class="img-profile rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
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
                            <span class="badge badge-primary px-3 py-2">{{ $order->driver->balance }}</span>
                        </li>
                    </ul>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('drivers.show', $order->driver_id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user"></i> {{ __('messages.View_Profile') }}
                        </a>
                        <a href="{{ route('orders.driverOrders', $order->driver_id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list"></i> {{ __('messages.View_Orders') }}
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
    // Show/hide cancellation reason field based on status
    $(document).ready(function() {
        $('#status').on('change', function() {
            var status = $(this).val();
            if (status == '6' || status == '7') {
                $('.cancel-reason-container').show();
            } else {
                $('.cancel-reason-container').hide();
            }
        });
        
        // Initialize map if Google Maps API is loaded
        if (typeof google !== 'undefined') {
            initMap();
        }
    });
    
    // Initialize map to show route
    function initMap() {
        var pickupLat = {{ $order->pick_lat }};
        var pickupLng = {{ $order->pick_lng }};
        var dropLat = {{ $order->drop_lat }};
        var dropLng = {{ $order->drop_lng }};
        
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: {lat: (pickupLat + dropLat) / 2, lng: (pickupLng + dropLng) / 2}
        });
        
        var pickupMarker = new google.maps.Marker({
            position: {lat: pickupLat, lng: pickupLng},
            map: map,
            title: '{{ $order->pick_name }}',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
            }
        });
        
        var dropMarker = new google.maps.Marker({
            position: {lat: dropLat, lng: dropLng},
            map: map,
            title: '{{ $order->drop_name }}',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
            }
        });
        
        var directionsService = new google.maps.DirectionsService();
        var directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#4e73df',
                strokeWeight: 5
            }
        });
        
        directionsRenderer.setMap(map);
        
        var request = {
            origin: {lat: pickupLat, lng: pickupLng},
            destination: {lat: dropLat, lng: dropLng},
            travelMode: 'DRIVING'
        };
        
        directionsService.route(request, function(result, status) {
            if (status == 'OK') {
                directionsRenderer.setDirections(result);
            }
        });
    }
</script>

<!-- Optional: Load Google Maps API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
@endsection