@extends('layouts.admin')

@section('title', __('messages.Edit_Order'))

@section('content')
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
                        <!-- User and Service Selection -->
                        <div class="form-group">
                            <label for="user_id">{{ __('messages.User') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">{{ __('messages.Select_User') }}</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $order->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="driver_id">{{ __('messages.Driver') }}</label>
                            <select class="form-control" id="driver_id" name="driver_id">
                                <option value="">{{ __('messages.Select_Driver') }}</option>
                                @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id', $order->driver_id) == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} ({{ $driver->phone }})
                                </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('messages.Driver_Selection_Info') }}</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="service_id">{{ __('messages.Service') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="service_id" name="service_id" required>
                                <option value="">{{ __('messages.Select_Service') }}</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id', $order->service_id) == $service->id ? 'selected' : '' }}
                                        data-price="{{ $service->start_price }}" data-price-km="{{ $service->price_per_km }}" 
                                        data-commission="{{ $service->admin_commision }}" data-commission-type="{{ $service->type_of_commision }}">
                                    {{ $service->name_en }} ({{ $service->name_ar }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                     @php
                        use App\Enums\OrderStatus;
                        use App\Enums\PaymentMethod;
                        use App\Enums\StatusPayment;
                     @endphp
                    
                    <!-- Order Status -->
                    <div class="form-group">
                        <label for="status">{{ __('messages.Status') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            @foreach(OrderStatus::cases() as $status)
                                <option value="{{ $status->value }}" 
                                    {{ old('status', $order->status->value ?? '') === $status->value ? 'selected' : '' }}>
                                    {{ __('messages.' . Str::studly($status->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Cancel Reason (shown only when status is a cancel type) -->
                    <div class="form-group cancel-reason-container" 
                         style="display: {{ in_array(old('status', $order->status->value ?? ''), ['user_cancel_order', 'driver_cancel_order']) ? 'block' : 'none' }};">
                        <label for="reason_for_cancel">{{ __('messages.Cancellation_Reason') }}</label>
                        <textarea class="form-control" id="reason_for_cancel" name="reason_for_cancel" rows="2">{{ old('reason_for_cancel', $order->reason_for_cancel) }}</textarea>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="form-group">
                        <label for="payment_method">{{ __('messages.Payment_Method') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            @foreach(PaymentMethod::cases() as $method)
                                <option value="{{ $method->value }}" 
                                    {{ old('payment_method', $order->payment_method->value ?? '') === $method->value ? 'selected' : '' }}>
                                    {{ __('messages.' . Str::studly($method->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Payment Status -->
                    <div class="form-group">
                        <label for="status_payment">{{ __('messages.Payment_Status') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="status_payment" name="status_payment" required>
                            @foreach(StatusPayment::cases() as $status)
                                <option value="{{ $status->value }}" 
                                    {{ old('status_payment', $order->status_payment->value ?? '') === $status->value ? 'selected' : '' }}>
                                    {{ __('messages.' . Str::studly($status->name)) }}
                                </option>
                            @endforeach
                        </select>
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
                                    <label for="pick_name">{{ __('messages.Pickup_Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pick_name" name="pick_name" value="{{ old('pick_name', $order->pick_name) }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pick_lat">{{ __('messages.Latitude') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="any" class="form-control" id="pick_lat" name="pick_lat" value="{{ old('pick_lat', $order->pick_lat) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pick_lng">{{ __('messages.Longitude') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="any" class="form-control" id="pick_lng" name="pick_lng" value="{{ old('pick_lng', $order->pick_lng) }}" required>
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
                                    <label for="drop_name">{{ __('messages.Dropoff_Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="drop_name" name="drop_name" value="{{ old('drop_name', $order->drop_name) }}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="drop_lat">{{ __('messages.Latitude') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="any" class="form-control" id="drop_lat" name="drop_lat" value="{{ old('drop_lat', $order->drop_lat) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="drop_lng">{{ __('messages.Longitude') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="any" class="form-control" id="drop_lng" name="drop_lng" value="{{ old('drop_lng', $order->drop_lng) }}" required>
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
                                            <label for="total_price_before_discount">{{ __('messages.Original_Price') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="total_price_before_discount" name="total_price_before_discount" value="{{ old('total_price_before_discount', $order->total_price_before_discount) }}" required min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="discount_value">{{ __('messages.Discount') }}</label>
                                            <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" value="{{ old('discount_value', $order->discount_value) }}" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="total_price_after_discount">{{ __('messages.Final_Price') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="total_price_after_discount" name="total_price_after_discount" value="{{ old('total_price_after_discount', $order->total_price_after_discount) }}" required min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="commision_of_admin">{{ __('messages.Admin_Commission') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="commision_of_admin" name="commision_of_admin" value="{{ old('commision_of_admin', $order->commision_of_admin) }}" required min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="net_price_for_driver">{{ __('messages.Driver_Earning') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="net_price_for_driver" name="net_price_for_driver" value="{{ old('net_price_for_driver', $order->net_price_for_driver) }}" required min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div id="map" style="height: 200px; width: 100%; margin-bottom: 20px;"></div>
                    </div>

                    <!-- NEW: Waiting Charges Information -->
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
                                        <input type="datetime-local" class="form-control" id="arrived_at" name="arrived_at" 
                                            value="{{ old('arrived_at', $order->arrived_at ? $order->arrived_at->format('Y-m-d\TH:i') : '') }}">
                                        <small class="form-text text-muted">{{ __('messages.Arrived_At_Info') }}</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total_waiting_minutes">{{ __('messages.Total_Waiting_Minutes') }}</label>
                                        <input type="number" class="form-control" id="total_waiting_minutes" name="total_waiting_minutes" 
                                            value="{{ old('total_waiting_minutes', $order->total_waiting_minutes) }}" min="0">
                                        <small class="form-text text-muted">{{ __('messages.Total_Waiting_Minutes_Info') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="waiting_charges">{{ __('messages.Waiting_Charges') }}</label>
                                        <input type="number" step="0.01" class="form-control" id="waiting_charges" name="waiting_charges" 
                                            value="{{ old('waiting_charges', $order->waiting_charges) }}" min="0">
                                        <small class="form-text text-muted">{{ __('messages.Waiting_Charges_Info') }}</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">
                            <h6 class="font-weight-bold text-warning mb-3">
                                <i class="fas fa-traffic-light"></i> {{ __('messages.In_Trip_Waiting') }}
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="in_trip_waiting_minutes">{{ __('messages.In_Trip_Waiting_Minutes') }}</label>
                                        <input type="number" class="form-control" id="in_trip_waiting_minutes" name="in_trip_waiting_minutes" 
                                            value="{{ old('in_trip_waiting_minutes', $order->in_trip_waiting_minutes) }}" min="0">
                                        <small class="form-text text-muted">{{ __('messages.In_Trip_Waiting_Minutes_Info') }}</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="in_trip_waiting_charges">{{ __('messages.In_Trip_Waiting_Charges') }}</label>
                                        <input type="number" step="0.01" class="form-control" id="in_trip_waiting_charges" name="in_trip_waiting_charges" 
                                            value="{{ old('in_trip_waiting_charges', $order->in_trip_waiting_charges) }}" min="0">
                                        <small class="form-text text-muted">{{ __('messages.In_Trip_Waiting_Charges_Info') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> 
                                <strong>{{ __('messages.Note') }}:</strong> {{ __('messages.Waiting_Charges_Note') }}
                            </div>
                        </div>
                    </div>

                    <div id="map" style="height: 200px; width: 100%; margin-bottom: 20px;"></div>
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
        // Initialize map if Google Maps API is loaded
        if (typeof google !== 'undefined') {
            initMap();
        }
        
        // Show/hide cancellation reason field based on status
        $('#status').on('change', function() {
            var status = $(this).val();
            if (status == '6' || status == '7') {
                $('.cancel-reason-container').show();
                $('#reason_for_cancel').prop('required', true);
            } else {
                $('.cancel-reason-container').hide();
                $('#reason_for_cancel').prop('required', false);
            }
        });
        
        // Calculate distance and price when coordinates change
        $('#pick_lat, #pick_lng, #drop_lat, #drop_lng, #service_id').on('change', function() {
            calculateDistance();
        });
        
        // Calculate final price when discount changes
        $('#discount_value').on('change', function() {
            calculatePrices();
        });
    });
    
    // Calculate distance between pickup and dropoff
    function calculateDistance() {
        var pickLat = parseFloat($('#pick_lat').val()) || 0;
        var pickLng = parseFloat($('#pick_lng').val()) || 0;
        var dropLat = parseFloat($('#drop_lat').val()) || 0;
        var dropLng = parseFloat($('#drop_lng').val()) || 0;
        
        if (pickLat && pickLng && dropLat && dropLng) {
            // Haversine formula to calculate distance
            var earthRadius = 6371; // Radius of the earth in km
            var latDelta = deg2rad(dropLat - pickLat);
            var lngDelta = deg2rad(dropLng - pickLng);
            var a = Math.sin(latDelta/2) * Math.sin(latDelta/2) +
                    Math.cos(deg2rad(pickLat)) * Math.cos(deg2rad(dropLat)) *
                    Math.sin(lngDelta/2) * Math.sin(lngDelta/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            var distance = earthRadius * c;
            
            // Round to 2 decimal places
            distance = Math.round(distance * 100) / 100;
            
            // Calculate prices based on service selected
            var serviceId = $('#service_id').val();
            if (serviceId) {
                var option = $('#service_id option:selected');
                var startPrice = parseFloat(option.data('price')) || 0;
                var pricePerKm = parseFloat(option.data('price-km')) || 0;
                
                var totalBeforeDiscount = startPrice + (pricePerKm * distance);
                $('#total_price_before_discount').val(totalBeforeDiscount.toFixed(2));
                
                calculatePrices();
            }
            
            // Update map if available
            if (typeof google !== 'undefined') {
                updateMap(pickLat, pickLng, dropLat, dropLng);
            }
        }
    }
    
    // Calculate prices after discount and commission
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
        
        // Calculate commission and driver earning
        var serviceId = $('#service_id').val();
        if (serviceId) {
            var option = $('#service_id option:selected');
            var commissionValue = parseFloat(option.data('commission')) || 0;
            var commissionType = option.data('commission-type');
            var adminCommission = 0;
            
            if (commissionType == 1) {
                // Fixed amount
                adminCommission = commissionValue;
            } else {
                // Percentage
                adminCommission = (commissionValue / 100) * totalAfterDiscount;
            }
            
            $('#commision_of_admin').val(adminCommission.toFixed(2));
            $('#net_price_for_driver').val((totalAfterDiscount - adminCommission).toFixed(2));
        }
    }
    
    // Helper function to convert degrees to radians
    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }
    
    // Initialize and update map
    var map, pickupMarker, dropMarker, directionsRenderer;
    
    function initMap() {
        var pickLat = {{ $order->pick_lat }};
        var pickLng = {{ $order->pick_lng }};
        var dropLat = {{ $order->drop_lat }};
        var dropLng = {{ $order->drop_lng }};
        
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: {lat: (pickLat + dropLat) / 2, lng: (pickLng + dropLng) / 2}
        });
        
        pickupMarker = new google.maps.Marker({
            position: {lat: pickLat, lng: pickLng},
            map: map,
            title: '{{ $order->pick_name }}',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
            }
        });
        
        dropMarker = new google.maps.Marker({
            position: {lat: dropLat, lng: dropLng},
            map: map,
            title: '{{ $order->drop_name }}',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
            }
        });
        
        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#4e73df',
                strokeWeight: 5
            }
        });
        
        directionsRenderer.setMap(map);
        
        // Display route
        var directionsService = new google.maps.DirectionsService();
        var request = {
            origin: {lat: pickLat, lng: pickLng},
            destination: {lat: dropLat, lng: dropLng},
            travelMode: 'DRIVING'
        };
        
        directionsService.route(request, function(result, status) {
            if (status == 'OK') {
                directionsRenderer.setDirections(result);
            }
        });
    }
    
    function updateMap(pickLat, pickLng, dropLat, dropLng) {
        pickupMarker.setPosition({lat: pickLat, lng: pickLng});
        dropMarker.setPosition({lat: dropLat, lng: dropLng});
        
        map.setCenter({
            lat: (pickLat + dropLat) / 2,
            lng: (pickLng + dropLng) / 2
        });
        
        var directionsService = new google.maps.DirectionsService();
        var request = {
            origin: {lat: pickLat, lng: pickLng},
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