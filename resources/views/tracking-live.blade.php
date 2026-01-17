<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Order #{{ $order->number }}</title>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=geometry"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        #map {
            height: 60vh;
            width: 100%;
        }

        .info-panel {
            background: white;
            padding: 20px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-active {
            background: #4CAF50;
            color: white;
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 10px;
            margin-top: 15px;
        }

        .driver-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .locations {
            margin-top: 20px;
        }

        .location-item {
            padding: 10px;
            margin: 10px 0;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
            border-radius: 5px;
        }

        .location-item.dropoff {
            border-left-color: #ff5722;
        }

        .refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 20px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }

        .refresh-indicator.active {
            display: block;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .error-message {
            background: #ff5252;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Track Your Order</h1>
        <p>Order #{{ $order->number }}</p>
    </div>

    <div id="map"></div>

    <div class="refresh-indicator pulse" id="refreshIndicator">
        <span>🔄 Updating...</span>
    </div>

    <div class="info-panel">
        <div id="errorMessage" class="error-message"></div>
        
        <span class="status-badge status-active" id="statusBadge">
            {{ $order->getTrackingStatusText() }}
        </span>

        <div id="driverInfo" style="display: none;">
            <!-- Driver info will be populated by JavaScript -->
        </div>

        <div class="locations">
            <div class="location-item pickup">
                <strong>📍 Pickup Location</strong><br>
                {{ $order->pick_name }}
            </div>
            @if($order->drop_name)
            <div class="location-item dropoff">
                <strong>🎯 Dropoff Location</strong><br>
                {{ $order->drop_name }}
            </div>
            @endif
        </div>
    </div>

    <script>
        let map;
        let driverMarker;
        let pickupMarker;
        let dropoffMarker;
        let routeLine;
        let trackingToken = '{{ $order->tracking_token }}';
        let updateInterval;

        function initMap() {
            // Initialize map centered on pickup location
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: { lat: {{ $order->pick_lat }}, lng: {{ $order->pick_lng }} },
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ]
            });

            // Add pickup marker
            pickupMarker = new google.maps.Marker({
                position: { lat: {{ $order->pick_lat }}, lng: {{ $order->pick_lng }} },
                map: map,
                title: 'Pickup Location',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: '#4CAF50',
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2
                }
            });

            // Add dropoff marker if exists
            @if($order->drop_lat && $order->drop_lng)
            dropoffMarker = new google.maps.Marker({
                position: { lat: {{ $order->drop_lat }}, lng: {{ $order->drop_lng }} },
                map: map,
                title: 'Dropoff Location',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: '#ff5722',
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2
                }
            });

            // Draw route line
            routeLine = new google.maps.Polyline({
                path: [
                    { lat: {{ $order->pick_lat }}, lng: {{ $order->pick_lng }} },
                    { lat: {{ $order->drop_lat }}, lng: {{ $order->drop_lng }} }
                ],
                geodesic: true,
                strokeColor: '#4CAF50',
                strokeOpacity: 0.5,
                strokeWeight: 3,
                map: map
            });
            @endif

            // Start tracking
            updateDriverLocation();
            updateInterval = setInterval(updateDriverLocation, 5000); // Update every 5 seconds
        }

        async function updateDriverLocation() {
            const indicator = document.getElementById('refreshIndicator');
            indicator.classList.add('active');

            try {
                const response = await fetch(`/api/track-order/${trackingToken}`);
                const result = await response.json();

                if (result.success) {
                    const data = result.data;

                    // Update status
                    document.getElementById('statusBadge').textContent = data.order.status_text;

                    // Update driver info
                    if (data.driver) {
                        updateDriverInfo(data.driver);

                        // Update driver marker
                        if (data.driver.location) {
                            updateDriverMarker(data.driver.location);
                        }
                    }
                } else {
                    showError(result.message);
                    clearInterval(updateInterval);
                }
            } catch (error) {
                console.error('Error updating location:', error);
                showError('Failed to update location');
            } finally {
                setTimeout(() => {
                    indicator.classList.remove('active');
                }, 500);
            }
        }

        function updateDriverInfo(driver) {
            const driverInfoDiv = document.getElementById('driverInfo');
            driverInfoDiv.style.display = 'block';
            driverInfoDiv.innerHTML = `
                <div class="driver-info">
                    <img src="${driver.photo || '/assets/default-avatar.png'}" alt="${driver.name}" class="driver-photo">
                    <div>
                        <strong>${driver.name}</strong><br>
                        <small>${driver.car_info.model || ''} ${driver.car_info.color || ''}</small><br>
                        <small>${driver.car_info.plate_number || ''}</small>
                    </div>
                </div>
            `;
        }

        function updateDriverMarker(location) {
            if (!driverMarker) {
                // Create driver marker
                driverMarker = new google.maps.Marker({
                    position: { lat: location.lat, lng: location.lng },
                    map: map,
                    title: 'Driver Location',
                    icon: {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        scale: 5,
                        fillColor: '#2196F3',
                        fillOpacity: 1,
                        strokeColor: '#fff',
                        strokeWeight: 2,
                        rotation: 0
                    }
                });
            } else {
                // Update position with animation
                const newPosition = new google.maps.LatLng(location.lat, location.lng);
                animateMarker(driverMarker, newPosition);
            }

            // Keep driver in view
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(pickupMarker.getPosition());
            if (dropoffMarker) bounds.extend(dropoffMarker.getPosition());
            bounds.extend(driverMarker.getPosition());
            map.fitBounds(bounds);
        }

        function animateMarker(marker, newPosition) {
            const startPosition = marker.getPosition();
            const steps = 50;
            let step = 0;

            const latStep = (newPosition.lat() - startPosition.lat()) / steps;
            const lngStep = (newPosition.lng() - startPosition.lng()) / steps;

            const interval = setInterval(() => {
                step++;
                const lat = startPosition.lat() + (latStep * step);
                const lng = startPosition.lng() + (lngStep * step);
                marker.setPosition(new google.maps.LatLng(lat, lng));

                if (step >= steps) {
                    clearInterval(interval);
                }
            }, 100);
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        // Initialize map when page loads
        window.onload = initMap;

        // Cleanup on page unload
        window.onbeforeunload = function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        };
    </script>
</body>
</html>