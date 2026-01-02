<?php

namespace App\Services;

use App\Http\Controllers\Admin\FCMController as AdminFCMController;
use App\Models\Driver;
use App\Models\Order;
use App\Models\ServicePayment;
use App\Models\Service;
use App\Http\Controllers\FCMController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DriverLocationService
{
    protected $projectId;
    protected $baseUrl;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id');
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }


    /**
     * Find nearby drivers within a specific radius (for alerts, not orders)
     * This method only finds drivers and returns their information without writing to Firebase
     */
    public function findNearbyDrivers($lat, $lng, $radiusKm = 10)
    {
        try {
            \Log::info("Searching for drivers within {$radiusKm}km radius at location: {$lat}, {$lng}");

            // Get minimum wallet balance from settings
            $minWalletBalance = \DB::table('settings')
                ->where('key', 'minimum_money_in_wallet_driver_to_get_order')
                ->value('value') ?? 0;

            // Step 1: Get all online and active drivers from MySQL
            $availableDriverIds = Driver::where('status', 1) // online
                ->where('activate', 1) // active account
                ->where('balance', '>=', $minWalletBalance)
                ->pluck('id')
                ->toArray();

            if (empty($availableDriverIds)) {
                \Log::warning("No available drivers found in the system");
                return [
                    'success' => false,
                    'message' => 'No available drivers found',
                    'drivers' => [],
                    'count' => 0
                ];
            }

            \Log::info("Found " . count($availableDriverIds) . " available drivers in database");

            // Step 2: Get driver locations from Firestore
            $driversWithLocations = $this->getDriverLocationsFromFirestore($availableDriverIds);

            if (empty($driversWithLocations)) {
                \Log::warning("No drivers with active locations found in Firestore");
                return [
                    'success' => false,
                    'message' => 'No drivers with active locations found',
                    'drivers' => [],
                    'count' => 0
                ];
            }

            \Log::info("Found " . count($driversWithLocations) . " drivers with locations in Firestore");

            // Step 3: Filter and sort drivers by distance
            $nearbyDrivers = $this->sortDriversByDistance($driversWithLocations, $lat, $lng, $radiusKm);

            if (empty($nearbyDrivers)) {
                \Log::warning("No drivers found within {$radiusKm}km radius");
                return [
                    'success' => false,
                    'message' => "No drivers found within {$radiusKm}km radius",
                    'drivers' => [],
                    'count' => 0
                ];
            }

            // Extract only driver IDs for notification
            $driverIds = array_column($nearbyDrivers, 'id');

            \Log::info("Found " . count($nearbyDrivers) . " drivers within {$radiusKm}km radius");

            return [
                'success' => true,
                'message' => "Found " . count($nearbyDrivers) . " drivers within {$radiusKm}km",
                'drivers' => $nearbyDrivers, // Contains id, lat, lng, distance
                'driver_ids' => $driverIds, // Just the IDs for FCM
                'count' => count($nearbyDrivers),
                'radius_km' => $radiusKm
            ];
        } catch (\Exception $e) {
            \Log::error('Error in findNearbyDrivers: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error finding nearby drivers: ' . $e->getMessage(),
                'drivers' => [],
                'count' => 0
            ];
        }
    }

    /**
     * Progressive driver search with incremental radius expansion
     * Searches in zones (5km, 10km, 15km, etc.) with 30-second wait between zones
     */
    public function findAndStoreOrderInFirebase($userLat, $userLng, $orderId, $serviceId, $radius = null, $orderStatus = 'pending')
    {
        try {
            // Get radius settings from database
            $initialRadius = \DB::table('settings')
                ->where('key', 'find_drivers_in_radius')
                ->value('value') ?? 5;

            $maximumRadius = \DB::table('settings')
                ->where('key', 'maximum_radius_to_find_drivers')
                ->value('value') ?? 20;

            // Override with provided radius if available (for backward compatibility)
            if ($radius !== null) {
                $maximumRadius = $radius / 1000; // Convert meters to km if needed
            }

            // Create radius zones (5km, 10km, 15km, 20km, etc.)
            $radiusZones = [];
            for ($r = $initialRadius; $r <= $maximumRadius; $r += $initialRadius) {
                $radiusZones[] = $r;
            }

            // Ensure maximum radius is included if it's not a multiple of initial radius
            if (end($radiusZones) < $maximumRadius) {
                $radiusZones[] = $maximumRadius;
            }

            \Log::info("Starting progressive driver search for order {$orderId}. Zones: " . implode('km, ', $radiusZones) . 'km');

            // Step 1: Get available drivers from MySQL filtered by service
            $availableDriverIds = $this->getAvailableDriversForService($serviceId, $orderId);

            if (empty($availableDriverIds)) {
                \Log::warning("No available drivers found for service {$serviceId}. Checked criteria: online status, active account, sufficient balance, no active orders, service assignment.");
                return [
                    'success' => false,
                    'message' => 'No available drivers found for this service'
                ];
            }

            \Log::info("Found " . count($availableDriverIds) . " available drivers for service {$serviceId}");

            // Step 2: Get driver locations from Firestore using REST API
            $driversWithLocations = $this->getDriverLocationsFromFirestore($availableDriverIds);

            if (empty($driversWithLocations)) {
                \Log::warning("No drivers with active locations found in Firestore for service {$serviceId}");
                return [
                    'success' => false,
                    'message' => 'No drivers with active locations found for this service'
                ];
            }

            \Log::info("Found " . count($driversWithLocations) . " drivers with active locations in Firestore");

            // Step 3: Progressive search through each radius zone
            foreach ($radiusZones as $currentRadius) {
                \Log::info("Searching for drivers within {$currentRadius}km radius for order {$orderId}");

                // Calculate distances and sort for current radius
                $sortedDrivers = $this->sortDriversByDistance($driversWithLocations, $userLat, $userLng, $currentRadius);

                if (!empty($sortedDrivers)) {
                    \Log::info("Found " . count($sortedDrivers) . " drivers within {$currentRadius}km for order {$orderId}");

                    // Step 4: Write order data to Firebase
                    $firebaseResult = $this->writeOrderToFirebase($orderId, $sortedDrivers, $serviceId, $orderStatus, $currentRadius);

                    // Return success with zone information
                    return [
                        'success' => $firebaseResult['success'],
                        'drivers_found' => count($sortedDrivers),
                        'drivers' => $sortedDrivers,
                        'service_id' => $serviceId,
                        'search_radius' => $currentRadius,
                        'firebase_write' => $firebaseResult['success'] ? 'success' : 'failed',
                        'message' => $firebaseResult['message'] ?? "Order data processed in {$currentRadius}km radius",
                        'wait_time' => 30,
                        'next_radius' => $this->getNextRadius($currentRadius, $radiusZones, $maximumRadius)
                    ];
                }

                // If this is not the last zone, log and continue to next zone
                if ($currentRadius < $maximumRadius) {
                    \Log::info("No drivers found within {$currentRadius}km for order {$orderId}. Will search in next zone after timeout.");
                }
            }

            // If no drivers found in any zone
            return [
                'success' => false,
                'message' => "No drivers found within maximum radius of {$maximumRadius}km for this service",
                'searched_zones' => $radiusZones,
                'service_id' => $serviceId
            ];
        } catch (\Exception $e) {
            \Log::error('Error in findAndStoreOrderInFirebase: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error processing request: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get the next radius zone
     */
    private function getNextRadius($currentRadius, $radiusZones, $maximumRadius)
    {
        $currentIndex = array_search($currentRadius, $radiusZones);
        if ($currentIndex !== false && $currentIndex < count($radiusZones) - 1) {
            return $radiusZones[$currentIndex + 1];
        }
        return null;
    }

    /**
     * Get available drivers from MySQL filtered by service
     */
    private function getAvailableDriversForService($serviceId, $orderId = null)
    {
        $minWalletBalance = \DB::table('settings')
            ->where('key', 'minimum_money_in_wallet_driver_to_get_order')
            ->value('value') ?? 0;

        $query = Driver::where('status', 1)
            ->where('activate', 1)
            ->where('balance', '>=', $minWalletBalance)
            ->whereNotIn('id', function ($query) {
                $query->select('driver_id')
                    ->from('orders')
                    ->whereIn('status', ['pending', 'accepted', 'on_the_way', 'started', 'arrived'])
                    ->whereNotNull('driver_id');
            })
            ->whereHas('services', function ($query) use ($serviceId) {
                $query->where('service_id', $serviceId)
                    ->where('driver_services.status', 1);
            });

        // Exclude drivers who rejected this order
        if ($orderId) {
            $query->whereNotIn('id', function ($subQuery) use ($orderId) {
                $subQuery->select('driver_id')
                    ->from('order_rejections')
                    ->where('order_id', $orderId);
            });
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Get driver locations from Firestore using REST API with PAGINATION
     */
    private function getDriverLocationsFromFirestore(array $driverIds)
    {
        $driversWithLocations = [];

        try {
            $nextPageToken = null;
            $pageSize = 300; // Maximum allowed by Firebase
            
            do {
                // Build URL with pagination
                $url = "{$this->baseUrl}/drivers?pageSize={$pageSize}";
                if ($nextPageToken) {
                    $url .= "&pageToken=" . urlencode($nextPageToken);
                }
                
                $response = Http::timeout(10)->get($url);

                if (!$response->successful()) {
                    \Log::error('Failed to fetch drivers from Firestore: ' . $response->body());
                    break; // Stop pagination on error
                }

                $firestoreData = $response->json();

                // Process documents if they exist
                if (isset($firestoreData['documents']) && is_array($firestoreData['documents'])) {
                    foreach ($firestoreData['documents'] as $document) {
                        // Extract driver ID from document name
                        $nameParts = explode('/', $document['name']);
                        $driverId = (int)end($nameParts);

                        // Only process drivers that are in our available list
                        if (!in_array($driverId, $driverIds)) {
                            continue;
                        }

                        $fields = $document['fields'] ?? [];

                        // Get lat and lng from Firestore
                        $lat = $this->getFieldValue($fields, 'lat');
                        $lng = $this->getFieldValue($fields, 'lng');

                        // Check if location data exists and is valid
                        if (!empty($lat) && !empty($lng)) {
                            $driversWithLocations[] = [
                                'id' => $driverId,
                                'lat' => (float)$lat,
                                'lng' => (float)$lng,
                            ];
                        } else {
                            \Log::debug("Driver {$driverId} has no valid location data in Firestore");
                        }
                    }
                }
                
                // Check if there are more pages
                $nextPageToken = $firestoreData['nextPageToken'] ?? null;
                
            } while ($nextPageToken); // Continue if there's a next page
            
            \Log::info("Fetched " . count($driversWithLocations) . " drivers with valid locations from Firestore");
            
        } catch (\Exception $e) {
            \Log::error('Error fetching from Firestore: ' . $e->getMessage());
        }

        return $driversWithLocations;
    }

    /**
     * Helper method to extract value from Firestore field structure
     */
    private function getFieldValue($fields, $fieldName)
    {
        if (!isset($fields[$fieldName])) {
            return null;
        }

        $field = $fields[$fieldName];

        // Check for different value types
        if (isset($field['stringValue'])) {
            return $field['stringValue'];
        }
        if (isset($field['integerValue'])) {
            return $field['integerValue'];
        }
        if (isset($field['doubleValue'])) {
            return $field['doubleValue'];
        }
        if (isset($field['booleanValue'])) {
            return $field['booleanValue'];
        }
        if (isset($field['timestampValue'])) {
            return $field['timestampValue'];
        }

        return null;
    }

    /**
     * Convert PHP data to Firestore REST API format while maintaining original structure
     */
    private function convertToFirestoreFormat($data)
    {
        if (is_array($data)) {
            // Check if it's an associative array (map) or indexed array (list)
            if (array_keys($data) === range(0, count($data) - 1)) {
                // Indexed array - convert to Firestore array
                return [
                    'arrayValue' => [
                        'values' => array_map(function ($item) {
                            return $this->convertToFirestoreFormat($item);
                        }, $data)
                    ]
                ];
            } else {
                // Associative array - convert to Firestore map
                $fields = [];
                foreach ($data as $key => $value) {
                    $fields[$key] = $this->convertToFirestoreFormat($value);
                }
                return [
                    'mapValue' => [
                        'fields' => $fields
                    ]
                ];
            }
        } elseif (is_string($data)) {
            return ['stringValue' => $data];
        } elseif (is_int($data)) {
            return ['integerValue' => (string)$data];
        } elseif (is_float($data) || is_double($data)) {
            return ['doubleValue' => $data];
        } elseif (is_bool($data)) {
            return ['booleanValue' => $data];
        } elseif ($data instanceof \DateTime) {
            return ['timestampValue' => $data->format('c')];
        } elseif ($data === null) {
            return ['nullValue' => null];
        } else {
            return ['stringValue' => (string)$data];
        }
    }

    /**
     * Sort drivers by distance from user location
     */
    private function sortDriversByDistance(array $drivers, $userLat, $userLng, $maxRadius)
    {
        $driversWithDistance = [];

        foreach ($drivers as $driver) {
            $distance = $this->calculateDistance(
                $userLat,
                $userLng,
                $driver['lat'],
                $driver['lng']
            );

            // Only include drivers within the specified radius
            if ($distance <= $maxRadius) {
                $driver['distance'] = round($distance, 2);
                $driversWithDistance[] = $driver;
            }
        }

        // Sort by distance (nearest first)
        usort($driversWithDistance, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return $driversWithDistance;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
     * Write complete order data and user information to Firebase using REST API
     * Maintains the EXACT same structure as before for mobile app compatibility
     */
    private function writeOrderToFirebase($orderId, array $drivers, $serviceId, $orderStatus, $searchRadius = null)
    {
        try {
            // Get the complete order with user and service relationships
            $order = Order::with(['user', 'service'])->find($orderId);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            // Extract only driver IDs from the sorted drivers array
            $driverIDs = array_map(function ($driver) {
                return $driver['id'];
            }, $drivers);

            // Prepare complete order data with user information - SAME STRUCTURE AS BEFORE
            $orderData = [
                // Order basic information
                'ride_id' => $orderId,
                'order_number' => $order->number,
                'status' => $orderStatus,
                'service_id' => $serviceId,

                // User information
                'user_id' => $order->user_id,
                'user_info' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name ?? '',
                    'email' => $order->user->email ?? '',
                    'phone' => $order->user->phone ?? '',
                ],

                // Service information
                'service_info' => [
                    'id' => $order->service->id,
                    'name' => $order->service->name ?? '',
                    'type' => $order->service->type ?? '',
                    'waiting_time' => $order->service->waiting_time ?? '',
                ],

                // Location information
                'pickup_location' => [
                    'name' => $order->pick_name,
                    'latitude' => $order->pick_lat,
                    'longitude' => $order->pick_lng,
                ],
                'dropoff_location' => [
                    'name' => $order->drop_name,
                    'latitude' => $order->drop_lat,
                    'longitude' => $order->drop_lng,
                ],

                // Pricing information
                'pricing' => [
                    'total_price_before_discount' => $order->total_price_before_discount,
                    'discount_value' => $order->discount_value ?? 0,
                    'total_price_after_discount' => $order->total_price_after_discount,
                    'net_price_for_driver' => $order->net_price_for_driver,
                    'commission_of_admin' => $order->commision_of_admin,
                ],

                // Payment information
                'payment_info' => [
                    'payment_method' => $order->payment_method->value ?? 'cash',
                    'payment_status' => $order->status_payment->value ?? 'pending',
                ],

                // Driver information
                'driver_ids' => $driverIDs,
                'total_available_drivers' => count($driverIDs),
                'assigned_driver_id' => $order->driver_id,
                'search_radius_km' => $searchRadius,

                // Additional information
                'reason_for_cancel' => $order->reason_for_cancel,
                'distance' => $order->getDistance(),

                // Timestamps
                'created_at' => $order->created_at->toIso8601String(),
                'updated_at' => $order->updated_at->toIso8601String(),
                'firebase_created_at' => now()->toIso8601String(),
                'firebase_updated_at' => now()->toIso8601String(),
            ];

            // Convert to Firestore format
            $firestoreData = [
                'fields' => $this->convertToFirestoreFormat($orderData)['mapValue']['fields']
            ];

            // Write to Firestore using PATCH to create or update
            $response = Http::timeout(10)->patch(
                "{$this->baseUrl}/ride_requests/{$orderId}",
                $firestoreData
            );

            if ($response->successful()) {
                \Log::info("Complete order data for order {$orderId} written to Firebase with " . count($driverIDs) . " available drivers within {$searchRadius}km for service {$serviceId}");

                return [
                    'success' => true,
                    'message' => 'Complete order data successfully written to Firebase',
                    'drivers_count' => count($driverIDs),
                    'search_radius' => $searchRadius,
                ];
            } else {
                \Log::error("Failed to write order {$orderId} to Firebase: " . $response->body());
                return [
                    'success' => false,
                    'message' => 'Failed to write order data to Firebase: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            \Log::error("Error writing complete order {$orderId} to Firebase: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to write complete order data to Firebase: ' . $e->getMessage()
            ];
        }
    }
}
