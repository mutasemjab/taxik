@extends('layouts.admin')

@section('title', __('messages.live_map'))

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <style>
        #driverMap {
            height: 600px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .map-container {
            position: relative;
        }

        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .driver-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .stat-card.online .value {
            color: #28a745;
        }

        .stat-card.offline .value {
            color: #dc3545;
        }

        .last-update {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .refresh-indicator {
            display: inline-block;
            margin-left: 10px;
        }

        .refresh-indicator.active {
            color: #28a745;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .driver-marker {
            background-color: #28a745;
            border: 3px solid white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .driver-marker.offline {
            background-color: #dc3545;
        }

        .driver-popup {
            min-width: 200px;
        }

        .driver-popup .driver-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .driver-popup .driver-info {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .status-badge.online {
            background-color: #28a745;
            color: white;
        }

        .status-badge.offline {
            background-color: #dc3545;
            color: white;
        }

        /* New styles for drivers without location */
        .no-location-table {
            width: 100%;
            margin-top: 15px;
            background: white;
            border-radius: 4px;
            overflow: hidden;
        }

        .no-location-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-location-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            font-size: 14px;
        }

        .no-location-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }

        .no-location-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .no-location-table tbody tr:last-child td {
            border-bottom: none;
        }

        .driver-status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .driver-status-badge.online {
            background-color: #d4edda;
            color: #155724;
        }

        .driver-status-badge.offline {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header mb-4">
                    <h1>{{ __('messages.live_map') }}</h1>
                    <p class="text-muted">{{ __('messages.map_description') }}</p>
                </div>

                <!-- Statistics Cards -->
                <div class="driver-stats">
                    <div class="stat-card online">
                        <h3>{{ __('messages.online_drivers') }}</h3>
                        <div class="value" id="onlineCount">0</div>
                    </div>
                    <div class="stat-card offline">
                        <h3>{{ __('messages.offline_drivers') }}</h3>
                        <div class="value" id="offlineCount">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>{{ __('messages.total_drivers') }}</h3>
                        <div class="value" id="totalCount">0</div>
                    </div>
                    <div class="stat-card" style="background-color: #fff3cd;">
                        <h3>{{ __('messages.drivers_without_location') }}</h3>
                        <div class="value" id="noLocationCount" style="color: #856404;">0</div>
                    </div>
                </div>



                <!-- Last Update Info -->
                <div class="last-update">
                    <i class="fas fa-clock"></i>
                    {{ __('messages.last_update') }}: <strong id="lastUpdateTime">--:--</strong>
                    <span class="refresh-indicator" id="refreshIndicator">
                        <i class="fas fa-sync-alt"></i>
                    </span>
                </div>

                <!-- Map Container -->
                <div class="map-container mt-3">
                    <div class="map-controls">
                        <button class="btn btn-primary btn-sm" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i> {{ __('messages.refresh_now') }}
                        </button>
                        <button class="btn btn-secondary btn-sm" id="centerMapBtn">
                            <i class="fas fa-crosshairs"></i> {{ __('messages.center_map') }}
                        </button>
                    </div>
                    <div id="driverMap"></div>
                </div>

                <!-- Drivers Without Location Section -->
                <div class="alert alert-warning mt-3" id="noLocationAlert" style="display: none;">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('messages.drivers_without_firebase_location') }}
                    </h5>
                    <p class="mb-2">{{ __('messages.drivers_without_location_description') }}</p>
                    <div id="noLocationDriversList"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        let map;
        let markers = {};
        let refreshInterval;
        const REFRESH_INTERVAL = 120000; // 2 minutes in milliseconds

        // Initialize map
        function initMap() {
            const defaultLat = 31.9454; // Amman, Jordan
            const defaultLng = 35.9284;

            map = L.map('driverMap').setView([defaultLat, defaultLng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);
        }

        // Create custom marker icon
        function createMarkerIcon(isOnline) {
            return L.divIcon({
                className: 'driver-marker' + (isOnline ? '' : ' offline'),
                iconSize: [30, 30],
                iconAnchor: [15, 15],
                popupAnchor: [0, -15]
            });
        }

        // Create popup content
        function createPopupContent(driver) {
            const statusClass = driver.status === 'online' ? 'online' : 'offline';
            const statusText = driver.status === 'online' ? '{{ __('messages.online') }}' :
                '{{ __('messages.offline') }}';

            return `
            <div class="driver-popup">
                <div class="driver-name">${driver.name}</div>
                <div class="driver-info">
                    <i class="fas fa-phone"></i> ${driver.phone || '{{ __('messages.no_phone') }}'}
                </div>
                <div class="driver-info">
                    <i class="fas fa-wallet"></i> {{ __('messages.balance') }}: ${driver.balance} {{ __('messages.currency') }}
                </div>
                <div class="status-badge ${statusClass}">
                    ${statusText}
                </div>
            </div>
        `;
        }

        // Update markers on map
        function updateMarkers(drivers) {
            // Clear existing markers
            Object.values(markers).forEach(marker => {
                map.removeLayer(marker);
            });
            markers = {};

            // Add new markers only for drivers with locations
            drivers.forEach(driver => {
                // Skip drivers without location
                if (!driver.has_location || driver.lat === null || driver.lng === null) {
                    return;
                }

                const isOnline = driver.status === 'online';
                const marker = L.marker([driver.lat, driver.lng], {
                    icon: createMarkerIcon(isOnline)
                }).addTo(map);

                marker.bindPopup(createPopupContent(driver));
                markers[driver.id] = marker;
            });
        }

        // Update statistics
        function updateStats(drivers) {
            const onlineDrivers = drivers.filter(d => d.status === 'online');
            const offlineDrivers = drivers.filter(d => d.status === 'offline');
            const noLocationDrivers = drivers.filter(d => !d.has_location);

            document.getElementById('onlineCount').textContent = onlineDrivers.length;
            document.getElementById('offlineCount').textContent = offlineDrivers.length;
            document.getElementById('totalCount').textContent = drivers.length;
            document.getElementById('noLocationCount').textContent = noLocationDrivers.length;
        }

        // Display drivers without location
        // Display drivers without location
        function displayDriversWithoutLocation(drivers) {
            const noLocationDrivers = drivers.filter(d => !d.has_location);
            const alert = document.getElementById('noLocationAlert');
            const listContainer = document.getElementById('noLocationDriversList');

            if (noLocationDrivers.length > 0) {
                alert.style.display = 'block';

                let tableHTML = `
            <div class="no-location-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.driver_id') }}</th>
                            <th>{{ __('messages.driver_name') }}</th>
                            <th>{{ __('messages.phone') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.balance') }}</th>
                            <th>{{ __('messages.reason') }}</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

                noLocationDrivers.forEach((driver, index) => {
                    const statusClass = driver.status === 'online' ? 'online' : 'offline';
                    const statusText = driver.status === 'online' ? '{{ __('messages.online') }}' :
                        '{{ __('messages.offline') }}';
                    const reason = driver.reason || 'Unknown';

                    tableHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${driver.id}</strong></td>
                    <td>${driver.name}</td>
                    <td>${driver.phone || '-'}</td>
                    <td><span class="driver-status-badge ${statusClass}">${statusText}</span></td>
                    <td>${driver.balance} {{ __('messages.currency') }}</td>
                    <td><small>${reason}</small></td>
                </tr>
            `;
                });

                tableHTML += `
                    </tbody>
                </table>
            </div>
        `;

                listContainer.innerHTML = tableHTML;
            } else {
                alert.style.display = 'none';
            }
        }

        // Fetch driver locations
        function fetchDriverLocations() {
            const indicator = document.getElementById('refreshIndicator');
            indicator.classList.add('active');

            fetch('{{ route('map.locations') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMarkers(data.drivers);
                        updateStats(data.drivers);
                        displayDriversWithoutLocation(data.drivers);

                        // Update last update time
                        const now = new Date();
                        const timeString = now.toLocaleTimeString('{{ app()->getLocale() }}');
                        document.getElementById('lastUpdateTime').textContent = timeString;

                        // Auto-fit map to show all markers on first load
                        if (Object.keys(markers).length > 0 && !map._initialFit) {
                            const group = new L.featureGroup(Object.values(markers));
                            map.fitBounds(group.getBounds().pad(0.1));
                            map._initialFit = true;
                        }

                        toastr.success('{{ __('messages.map_updated') }}');
                    } else {
                        console.error('Error fetching driver locations:', data.message);
                        toastr.error(data.message || '{{ __('messages.error_loading_locations') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('{{ __('messages.error_loading_locations') }}');
                })
                .finally(() => {
                    indicator.classList.remove('active');
                });
        }

        // Center map on all markers
        function centerMap() {
            if (Object.keys(markers).length > 0) {
                const group = new L.featureGroup(Object.values(markers));
                map.fitBounds(group.getBounds().pad(0.1));
                toastr.info('{{ __('messages.map_centered') }}');
            } else {
                toastr.info('{{ __('messages.no_drivers_to_center') }}');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            fetchDriverLocations();

            // Set up auto-refresh every 2 minutes
            refreshInterval = setInterval(fetchDriverLocations, REFRESH_INTERVAL);

            // Manual refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                fetchDriverLocations();
            });

            // Center map button
            document.getElementById('centerMapBtn').addEventListener('click', function() {
                centerMap();
            });
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
    </script>
@endsection
