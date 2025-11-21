<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DriverLocationService;
use App\Models\Order;
use App\Models\Driver;
use App\Enums\OrderStatus;

class SearchDriversInNextZone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $currentRadius;
    protected $serviceId;
    protected $userLat;
    protected $userLng;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $currentRadius, $serviceId, $userLat, $userLng)
    {
        $this->orderId = $orderId;
        $this->currentRadius = $currentRadius;
        $this->serviceId = $serviceId;
        $this->userLat = $userLat;
        $this->userLng = $userLng;
        
        // Delay this job by 30 seconds
        $this->delay(now()->addSeconds(30));
    }

    /**
     * Execute the job.
     */
    public function handle(DriverLocationService $driverLocationService)
    {
        try {
            // Check if order still exists and is pending
            $order = Order::find($this->orderId);
            
            if (!$order) {
                \Log::info("Order {$this->orderId} not found. Stopping driver search.");
                return;
            }
            
            // If order is no longer pending (accepted by driver), stop searching
            if ($order->status != OrderStatus::Pending) {
                \Log::info("Order {$this->orderId} is no longer pending (status: {$order->status->value}). Stopping driver search.");
                return;
            }
            
            // Get radius settings
            $initialRadius = \DB::table('settings')
                ->where('key', 'find_drivers_in_radius')
                ->value('value') ?? 5;
            
            $maximumRadius = \DB::table('settings')
                ->where('key', 'maximum_radius_to_find_drivers')
                ->value('value') ?? 20;
            
            // Calculate next radius
            $nextRadius = $this->currentRadius + $initialRadius;
            
            // If we've reached maximum radius, stop
            if ($nextRadius > $maximumRadius) {
                \Log::info("Reached maximum search radius ({$maximumRadius}km) for order {$this->orderId}. No drivers found.");
                
                // Optional: Update order status or notify user
                // $order->update(['status' => OrderStatus::CancelCronJob]);
                
                return;
            }
            
            \Log::info("Searching for drivers in next zone ({$nextRadius}km) for order {$this->orderId} after 30-second wait");
            
            // Search in next radius zone
            $this->searchInRadiusZone($driverLocationService, $nextRadius, $maximumRadius, $initialRadius);
            
        } catch (\Exception $e) {
            \Log::error("Error in SearchDriversInNextZone job for order {$this->orderId}: " . $e->getMessage());
        }
    }
    
    /**
     * Search for drivers in specific radius zone
     */
    private function searchInRadiusZone($driverLocationService, $currentRadius, $maximumRadius, $initialRadius)
    {
        // Get available drivers with STRICT CRITERIA
        $availableDriverIds = $this->getAvailableDriversForService($this->serviceId);
        
        if (empty($availableDriverIds)) {
            \Log::info("No available drivers for service {$this->serviceId} in {$currentRadius}km zone");
            
            // Continue to next zone if not at maximum
            if ($currentRadius < $maximumRadius) {
                SearchDriversInNextZone::dispatch(
                    $this->orderId,
                    $currentRadius,
                    $this->serviceId,
                    $this->userLat,
                    $this->userLng
                );
            }
            return;
        }
        
        \Log::info("Found " . count($availableDriverIds) . " available drivers for service {$this->serviceId}");
        
        // Get driver locations from Firestore
        $driversWithLocations = $this->getDriverLocationsFromFirestore($driverLocationService, $availableDriverIds);
        
        if (empty($driversWithLocations)) {
            \Log::info("No drivers with active locations found for {$currentRadius}km zone");
            
            // Continue to next zone if not at maximum
            if ($currentRadius < $maximumRadius) {
                SearchDriversInNextZone::dispatch(
                    $this->orderId,
                    $currentRadius,
                    $this->serviceId,
                    $this->userLat,
                    $this->userLng
                );
            }
            return;
        }
        
        // Sort drivers by distance for current radius
        $sortedDrivers = $this->sortDriversByDistance($driversWithLocations, $this->userLat, $this->userLng, $currentRadius);
        
        if (!empty($sortedDrivers)) {
            \Log::info("Found " . count($sortedDrivers) . " drivers within {$currentRadius}km for order {$this->orderId}");
            
            // Write order data to Firebase with new drivers
            $firebaseResult = $this->writeOrderToFirebase(
                $driverLocationService,
                $this->orderId, 
                $sortedDrivers, 
                $this->serviceId, 
                OrderStatus::Pending->value,
                $currentRadius
            );
            
            if ($firebaseResult['success']) {
                \Log::info("Successfully updated Firebase with drivers in {$currentRadius}km zone for order {$this->orderId}");
                
                // Schedule next search if not at maximum radius
                if ($currentRadius < $maximumRadius) {
                    SearchDriversInNextZone::dispatch(
                        $this->orderId,
                        $currentRadius,
                        $this->serviceId,
                        $this->userLat,
                        $this->userLng
                    );
                }
            }
        } else {
            \Log::info("No drivers found within {$currentRadius}km for order {$this->orderId}");
            
            // Continue to next zone if not at maximum
            if ($currentRadius < $maximumRadius) {
                SearchDriversInNextZone::dispatch(
                    $this->orderId,
                    $currentRadius,
                    $this->serviceId,
                    $this->userLat,
                    $this->userLng
                );
            }
        }
    }
    
    /**
     * Get available drivers for service with STRICT AVAILABILITY CHECKS
     * 
     * CHECKS:
     * ======
     * 1. Driver status = 1 (online)
     * 2. Driver activate = 1 (active account)
     * 3. Driver balance >= minimum required
     * 4. Driver NOT in orders with status: pending, accepted, on_the_way, started, arrived
     * 5. Driver has the service assigned with status = 1 in driver_services
     */
    private function getAvailableDriversForService($serviceId)
    {
        $minWalletBalance = \DB::table('settings')
            ->where('key', 'minimum_money_in_wallet_driver_to_get_order')
            ->value('value') ?? 0;
        
        return Driver::where('status', 1) // Must be online
            ->where('activate', 1) // Must be active
            ->where('balance', '>=', $minWalletBalance) // Must have sufficient balance
            
            // Exclude drivers with active orders (ALL busy statuses)
            ->whereNotIn('id', function($query) {
                $query->select('driver_id')
                    ->from('orders')
                    ->whereIn('status', [
                        'pending',      // OrderStatus::Pending
                        'accepted',     // OrderStatus::DriverAccepted
                        'on_the_way',   // OrderStatus::DriverGoToUser
                        'started',      // OrderStatus::UserWithDriver
                        'arrived'       // OrderStatus::Arrived
                    ])
                    ->whereNotNull('driver_id');
            })
            
            // Must have this service AND it must be active
            ->whereHas('services', function($query) use ($serviceId) {
                $query->where('service_id', $serviceId)
                      ->where('driver_services.status', 1); // Service must be active
            })
            ->pluck('id')
            ->toArray();
    }
    
    /**
     * Get driver locations from Firestore
     */
    private function getDriverLocationsFromFirestore($driverLocationService, array $driverIds)
    {
        // Use reflection to call the private method from DriverLocationService
        // Or you can make the method public/protected in DriverLocationService
        $driversWithLocations = [];
        
        try {
            $firestore = app(\Google\Cloud\Firestore\FirestoreClient::class);
            $collection = $firestore->database()->collection('drivers');
            
            foreach ($driverIds as $driverId) {
                $document = $collection->document((string)$driverId)->snapshot();
                
                if ($document->exists()) {
                    $data = $document->data();
                    
                    if (isset($data['lat']) && isset($data['lng']) && 
                        !empty($data['lat']) && !empty($data['lng'])) {
                        
                        $driversWithLocations[] = [
                            'id' => $driverId,
                            'lat' => (float)$data['lat'],
                            'lng' => (float)$data['lng'],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching from Firestore in job: ' . $e->getMessage());
        }
        
        return $driversWithLocations;
    }
    
    /**
     * Sort drivers by distance
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
            
            if ($distance <= $maxRadius) {
                $driver['distance'] = round($distance, 2);
                $driversWithDistance[] = $driver;
            }
        }
        
        usort($driversWithDistance, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        
        return $driversWithDistance;
    }
    
    /**
     * Calculate distance using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        
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
     * Write order to Firebase
     */
    private function writeOrderToFirebase($driverLocationService, $orderId, array $drivers, $serviceId, $orderStatus, $searchRadius)
    {
        try {
            $order = Order::with(['user', 'service'])->find($orderId);
            
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }
    
            $driverIDs = array_map(function($driver) {
                return $driver['id'];
            }, $drivers);
            
            $orderData = [
                'ride_id' => $orderId,
                'order_number' => $order->number,
                'status' => $orderStatus,
                'service_id' => $serviceId,
                'user_id' => $order->user_id,
                'user_info' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name ?? '',
                    'email' => $order->user->email ?? '',
                    'phone' => $order->user->phone ?? '',
                ],
                'service_info' => [
                    'id' => $order->service->id,
                    'name' => $order->service->name ?? '',
                    'type' => $order->service->type ?? '',
                    'waiting_time' => $order->service->waiting_time ?? '',
                ],
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
                'pricing' => [
                    'total_price_before_discount' => $order->total_price_before_discount,
                    'discount_value' => $order->discount_value ?? 0,
                    'total_price_after_discount' => $order->total_price_after_discount,
                    'net_price_for_driver' => $order->net_price_for_driver,
                    'commission_of_admin' => $order->commision_of_admin,
                ],
                'payment_info' => [
                    'payment_method' => $order->payment_method->value ?? 'cash',
                    'payment_status' => $order->status_payment->value ?? 'pending',
                ],
                'driver_ids' => $driverIDs,
                'total_available_drivers' => count($driverIDs),
                'assigned_driver_id' => $order->driver_id,
                'search_radius_km' => $searchRadius,
                'reason_for_cancel' => $order->reason_for_cancel,
                'distance' => $order->getDistance(),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'firebase_created_at' => new \DateTime(),
                'firebase_updated_at' => new \DateTime(),
            ];
            
            $firestore = app(\Google\Cloud\Firestore\FirestoreClient::class);
            $ordersCollection = $firestore->database()->collection('ride_requests');
            $ordersCollection->document((string)$orderId)->set($orderData);
            
            \Log::info("Order {$orderId} updated in Firebase with " . count($driverIDs) . " drivers at {$searchRadius}km");
            
            return [
                'success' => true,
                'message' => 'Order updated in Firebase',
                'drivers_count' => count($driverIDs),
                'search_radius' => $searchRadius
            ];
            
        } catch (\Exception $e) {
            \Log::error("Error writing order {$orderId} to Firebase: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to write to Firebase: ' . $e->getMessage()
            ];
        }
    }
}