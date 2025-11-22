<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

    public function __construct($orderId, $currentRadius, $serviceId, $userLat, $userLng)
    {
        $this->orderId = $orderId;
        $this->currentRadius = $currentRadius;
        $this->serviceId = $serviceId;
        $this->userLat = $userLat;
        $this->userLng = $userLng;
        
        $this->delay(now()->addSeconds(30));
    }

    public function handle()
    {
        try {
            $order = Order::find($this->orderId);
            
            if (!$order || $order->status != OrderStatus::Pending) {
                \Log::info("Order {$this->orderId} not pending. Stopping.");
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
                \Log::info("Max radius reached for order {$this->orderId}");
                return;
            }
            
            \Log::info("Expanding search to {$nextRadius}km for order {$this->orderId}");
            
            // ✅ Just check if drivers exist - don't use Firestore in Job
            $availableDrivers = $this->checkAvailableDrivers($this->serviceId, $nextRadius);
            
            if ($availableDrivers > 0) {
                \Log::info("Found {$availableDrivers} potential drivers in {$nextRadius}km - triggering web update");
                
                // ✅ Trigger a web request to update Firebase (has gRPC)
                $this->triggerFirebaseUpdate($nextRadius);
            }
            
            // Schedule next expansion
            if ($nextRadius < $maximumRadius) {
                SearchDriversInNextZone::dispatch(
                    $this->orderId,
                    $nextRadius,
                    $this->serviceId,
                    $this->userLat,
                    $this->userLng
                );
            }
            
        } catch (\Exception $e) {
            \Log::error("Error in SearchDriversInNextZone: " . $e->getMessage());
        }
    }
    
    /**
     * Check if drivers are available (MySQL only - no Firestore)
     */
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
        
        // ✅ Exclude drivers who rejected this order
        $query->whereNotIn('id', function($subQuery) {
            $subQuery->select('driver_id')
                ->from('order_rejections')
                ->where('order_id', $this->orderId);
        });
        
        return $query->count();
    }
    
    /**
     * Trigger Firebase update via HTTP request (runs in web context with gRPC)
     */
    private function triggerFirebaseUpdate($radius)
    {
        try {
            // Option 1: HTTP request to your own endpoint
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
            curl_close($ch);
            
            \Log::info("Triggered Firebase update via HTTP for order {$this->orderId}");
            
        } catch (\Exception $e) {
            \Log::error("Failed to trigger Firebase update: " . $e->getMessage());
        }
    }
}