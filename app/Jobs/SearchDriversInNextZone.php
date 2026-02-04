<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SearchDriversInNextZone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $currentRadius;
    protected $serviceId;
    protected $userLat;
    protected $userLng;

    public $tries = 1;
    public $timeout = 30;

    public function __construct($orderId, $currentRadius, $serviceId, $userLat, $userLng)
    {
        $this->orderId = $orderId;
        $this->currentRadius = $currentRadius;
        $this->serviceId = $serviceId;
        $this->userLat = $userLat;
        $this->userLng = $userLng;
    }

    public function handle()
    {
        try {
            $order = Order::find($this->orderId);
            
            if (!$order) {
                \Log::info("Job: Order {$this->orderId} not found. Stopping search.");
                return;
            }

            if ($order->status != OrderStatus::Pending) {
                \Log::info("Job: Order {$this->orderId} status changed to {$order->status->value}. Stopping search.");
                // ✅ Mark search as ended when order status changes
                app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
                return;
            }

            if ($order->driver_id) {
                \Log::info("Job: Order {$this->orderId} already assigned to driver {$order->driver_id}. Stopping search.");
                // ✅ Mark search as ended when driver is assigned
                app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
                return;
            }
            
            $initialRadius = \DB::table('settings')
                ->where('key', 'find_drivers_in_radius')
                ->value('value') ?? 5;
            
            $maximumRadius = \DB::table('settings')
                ->where('key', 'maximum_radius_to_find_drivers')
                ->value('value') ?? 20;
            
            $nextRadius = $this->currentRadius + $initialRadius;
            
            if ($nextRadius > $maximumRadius) {
                \Log::info("Job: Max radius {$maximumRadius}km reached for order {$this->orderId}. Stopping search.");
                // ✅ Mark search as ended when max radius is reached
                app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
                return;
            }
            
            \Log::info("Job: Expanding search to {$nextRadius}km for order {$this->orderId}");
            
            $order->refresh();
            if ($order->status != OrderStatus::Pending || $order->driver_id) {
                \Log::info("Job: Order {$this->orderId} status changed during job execution. Stopping.");
                // ✅ Mark search as ended
                app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
                return;
            }

            $availableDrivers = $this->checkAvailableDrivers($this->serviceId, $nextRadius);
            
            if ($availableDrivers > 0) {
                \Log::info("Job: Found {$availableDrivers} potential drivers in {$nextRadius}km - triggering web update for order {$this->orderId}");
                
                $order->refresh();
                if ($order->status != OrderStatus::Pending || $order->driver_id) {
                    \Log::info("Job: Order {$this->orderId} status changed before Firebase update. Stopping.");
                    // ✅ Mark search as ended
                    app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
                    return;
                }

                $this->triggerFirebaseUpdate($nextRadius);
            } else {
                \Log::info("Job: No drivers found in {$nextRadius}km for order {$this->orderId}");
            }
            
            // Schedule next expansion or end search
            if ($nextRadius < $maximumRadius) {
                $order->refresh();
                if ($order->status == OrderStatus::Pending && !$order->driver_id) {
                    SearchDriversInNextZone::dispatch(
                        $this->orderId,
                        $nextRadius,
                        $this->serviceId,
                        $this->userLat,
                        $this->userLng
                    )->delay(now()->addSeconds(30));
                    
                    \Log::info("Job: Scheduled next zone search for order {$this->orderId} at radius {$nextRadius}km + {$initialRadius}km");
                } else {
                    \Log::info("Job: Not scheduling next zone - order {$this->orderId} is no longer pending");
                    // ✅ Mark search as ended
                    app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
                }
            } else {
                // ✅ Reached max radius - mark search as ended
                \Log::info("Job: Reached maximum radius for order {$this->orderId}. Search complete.");
                app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
            }
            
        } catch (\Exception $e) {
            \Log::error("Job Error in SearchDriversInNextZone for order {$this->orderId}: " . $e->getMessage());
            // ✅ Mark search as ended on error
            try {
                app(\App\Services\DriverLocationService::class)->updateEndSearchFlag($this->orderId, true);
            } catch (\Exception $ex) {
                \Log::error("Failed to update end_search flag: " . $ex->getMessage());
            }
        }
    }
    
    private function checkAvailableDrivers($serviceId, $radius)
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
        
        $query->whereNotIn('id', function($subQuery) {
            $subQuery->select('driver_id')
                ->from('order_rejections')
                ->where('order_id', $this->orderId);
        });
        
        return $query->count();
    }
    
    private function triggerFirebaseUpdate($radius)
    {
        try {
            $url = config('app.url') . '/api/internal/update-order-radius';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'order_id' => $this->orderId,
                'radius' => $radius,
                'user_lat' => $this->userLat,
                'user_lng' => $this->userLng,
                'service_id' => $this->serviceId,
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                \Log::info("Job: Successfully triggered Firebase update for order {$this->orderId} at {$radius}km");
            } else {
                \Log::warning("Job: Firebase update returned HTTP {$httpCode} for order {$this->orderId}");
            }
            
        } catch (\Exception $e) {
            \Log::error("Job: Failed to trigger Firebase update for order {$this->orderId}: " . $e->getMessage());
        }
    }
}