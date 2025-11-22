<?php

namespace App\Services;

use App\Http\Controllers\Admin\FCMController as AdminFCMController;
use App\Models\Driver;
use App\Models\Order;
use App\Models\ServicePayment;
use App\Models\Service;
use App\Http\Controllers\FCMController;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Firestore;


class DriverLocationService
{
    protected $firestore;
    
    public function __construct() // ✅ No dependency
    {
        // Empty
    }

    private function getFirestore() // ✅ Lazy-load only when needed
    {
        if (!$this->firestore) {
            $this->firestore = app(Firestore::class);
        }
        return $this->firestore;
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
            // This already checks for:
            // - Driver status = 1 (online)
            // - Driver activate = 1 (active)
            // - Driver balance >= minimum
            // - Driver NOT in orders with status: pending, accepted, on_the_way, started, arrived
            // - Driver has the service active in driver_services table with status = 1
             $availableDriverIds = $this->getAvailableDriversForService($serviceId, $orderId);
            
            if (empty($availableDriverIds)) {
                \Log::warning("No available drivers found for service {$serviceId}. Checked criteria: online status, active account, sufficient balance, no active orders, service assignment.");
                return [
                    'success' => false,
                    'message' => 'No available drivers found for this service'
                ];
            }
            
            \Log::info("Found " . count($availableDriverIds) . " available drivers for service {$serviceId}");
            
            // Step 2: Get driver locations from Firestore
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
                        'wait_time' => 30, // Inform client to wait 30 seconds
                        'next_radius' => $this->getNextRadius($currentRadius, $radiusZones, $maximumRadius)
                    ];
                }
                
                // If this is not the last zone, log and continue to next zone
                if ($currentRadius < $maximumRadius) {
                    \Log::info("No drivers found within {$currentRadius}km for order {$orderId}. Will search in next zone after timeout.");
                    // Note: The 30-second wait will be handled by your job queue or client-side
                    // You should implement this using Laravel Queue with delayed jobs
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
        return null; // No next radius (reached maximum)
    }
    
    /**
     * Get available drivers from MySQL filtered by service
     * 
     * DRIVER AVAILABILITY CRITERIA:
     * ============================
     * 1. Driver status = 1 (online/available)
     * 2. Driver activate = 1 (account active)
     * 3. Driver balance >= minimum required balance from settings
     * 4. Driver NOT currently assigned to any active order with status:
     *    - Pending (pending)
     *    - DriverAccepted (accepted)
     *    - DriverGoToUser (on_the_way)
     *    - UserWithDriver (started)
     *    - Arrived (arrived)
     * 5. Driver has the requested service assigned in driver_services table
     * 6. The service assignment status in driver_services must be active (status = 1)
     * 
     * @param int $serviceId The service ID for the order
     * @return array Array of available driver IDs
     */
    private function getAvailableDriversForService($serviceId, $orderId = null)
    {
        $minWalletBalance = \DB::table('settings')
            ->where('key', 'minimum_money_in_wallet_driver_to_get_order')
            ->value('value') ?? 0;
        
        $query = Driver::where('status', 1)
            ->where('activate', 1)
            ->where('balance', '>=', $minWalletBalance)
            ->whereNotIn('id', function($query) {
                $query->select('driver_id')
                    ->from('orders')
                    ->whereIn('status', ['pending', 'accepted', 'on_the_way', 'started', 'arrived'])
                    ->whereNotNull('driver_id');
            })
            ->whereHas('services', function($query) use ($serviceId) {
                $query->where('service_id', $serviceId)
                    ->where('driver_services.status', 1);
            });
        
        // ✅ NEW: Exclude drivers who rejected this order
        if ($orderId) {
            $query->whereNotIn('id', function($subQuery) use ($orderId) {
                $subQuery->select('driver_id')
                    ->from('order_rejections')
                    ->where('order_id', $orderId);
            });
        }
        
        return $query->pluck('id')->toArray();
    }
    
    /**
     * Get driver locations from Firestore
     */
    private function getDriverLocationsFromFirestore(array $driverIds)
    {
        $driversWithLocations = [];
        
        try {
            // ✅ Use the lazy-loaded method
            $firestore = $this->getFirestore();
            $collection = $firestore->database()->collection('drivers');
            
            foreach ($driverIds as $driverId) {
                $document = $collection->document((string)$driverId)->snapshot();
                
                if ($document->exists()) {
                    $data = $document->data();
                    
                    // Check if location data exists and is valid
                    if (isset($data['lat']) && isset($data['lng']) && 
                        !empty($data['lat']) && !empty($data['lng'])) {
                        
                        $driversWithLocations[] = [
                            'id' => $driverId,
                            'lat' => (float)$data['lat'],
                            'lng' => (float)$data['lng'],
                        ];
                    } else {
                        \Log::debug("Driver {$driverId} has no valid location data in Firestore");
                    }
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error fetching from Firestore: ' . $e->getMessage());
        }
        
        return $driversWithLocations;
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
        usort($driversWithDistance, function($a, $b) {
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
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
    
    /**
     * Write complete order data and user information to Firebase orders collection
     * Updated to include current search radius
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
            $driverIDs = array_map(function($driver) {
                return $driver['id'];
            }, $drivers);
            
            // Prepare complete order data with user information
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
                    // Add any other user fields you need
                ],
                
                // Service information
                'service_info' => [
                    'id' => $order->service->id,
                    'name' => $order->service->name ?? '',
                    'type' => $order->service->type ?? '',
                    'waiting_time' => $order->service->waiting_time ?? '',
                    // Add any other service fields you need
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
                'search_radius_km' => $searchRadius, // Current search radius
                
                // Additional information
                'reason_for_cancel' => $order->reason_for_cancel,
                'distance' => $order->getDistance(), // Using the method from Order model
                
                // Timestamps
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'firebase_created_at' => new \DateTime(),
                'firebase_updated_at' => new \DateTime(),
            ];
            
               
            $firestore = $this->getFirestore();
            $ordersCollection = $firestore->database()->collection('ride_requests');
            $ordersCollection->document((string)$orderId)->set($orderData);

            \Log::info("Complete order data for order {$orderId} written to Firebase with " . count($driverIDs) . " available drivers within {$searchRadius}km for service {$serviceId}");

            // ✅ Only ONE return
            return [
                'success' => true,
                'message' => 'Complete order data successfully written to Firebase',
                'drivers_count' => count($driverIDs),
                'search_radius' => $searchRadius,
            ];
            
        } catch (\Exception $e) {
            \Log::error("Error writing complete order {$orderId} to Firebase: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to write complete order data to Firebase: ' . $e->getMessage()
            ];
        }
    }
}

