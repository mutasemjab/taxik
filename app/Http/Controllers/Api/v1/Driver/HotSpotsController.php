<?php

namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotSpotsController extends Controller
{
    use Responses;
    
    /**
     * Get today's hot spots (high demand areas)
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $radiusKm = $request->input('radius', 1);
        
        $hotSpots = $this->getTodayHotSpots($limit, $radiusKm);
        
        return $this->success_response('Hot spots retrieved successfully', $hotSpots);
    }
    
    /**
     * Get today's hot spots (high demand pickup locations)
     * Returns the top locations with most orders today
     */
    private function getTodayHotSpots($limit = 20, $radiusKm = 1)
    {
        $today = now()->format('Y-m-d');
        
        // Get all today's orders with pickup locations
        $todayOrders = DB::table('orders')
            ->whereDate('created_at', $today)
            ->whereNotNull('pick_lat')
            ->whereNotNull('pick_lng')
            ->select('pick_lat', 'pick_lng', 'pick_name')
            ->get();
        
        if ($todayOrders->isEmpty()) {
            return [
                'spots' => [],
                'total_spots' => 0,
                'message' => 'No hot spots available for today'
            ];
        }
        
        // Cluster nearby locations together
        $clusters = $this->clusterLocations($todayOrders, $radiusKm);
        
        // Sort clusters by order count (descending)
        usort($clusters, function($a, $b) {
            return $b['order_count'] <=> $a['order_count'];
        });
        
        // Get top spots
        $topSpots = array_slice($clusters, 0, $limit);
        
        // Format the response
        $formattedSpots = array_map(function($spot, $index) {
            return [
                'rank' => $index + 1,
                'location' => [
                    'latitude' => $spot['center_lat'],
                    'longitude' => $spot['center_lng'],
                    'area_name' => $spot['area_name']
                ],
                'order_count' => $spot['order_count'],
                'percentage' => $spot['percentage'],
                'demand_level' => $this->getDemandLevel($spot['order_count']),
                'recommended' => $index < 3 // Top 3 are highly recommended
            ];
        }, $topSpots, array_keys($topSpots));
        
        return [
            'spots' => $formattedSpots,
            'total_spots' => count($topSpots),
            'total_orders_today' => $todayOrders->count(),
            'last_updated' => now()->format('Y-m-d H:i:s'),
            'message' => 'These are the high-demand areas for today. Position yourself near these locations to receive more orders.'
        ];
    }
    
    /**
     * Cluster nearby locations together based on radius
     */
  
    private function clusterLocations($orders, $radiusKm = 1)
    {
        $clusters = [];
        $processed = [];
        
        foreach ($orders as $index => $order) {
            if (in_array($index, $processed)) {
                continue;
            }
            
            // This order becomes the center of a new cluster
            $clusterOrders = [$order];
            $clusterIndices = [$index];
            $processed[] = $index;
            
            // Find all other orders within radiusKm of this order
            foreach ($orders as $compareIndex => $compareOrder) {
                if (in_array($compareIndex, $processed)) {
                    continue;
                }
                
                $distance = $this->calculateDistance(
                    $order->pick_lat,
                    $order->pick_lng,
                    $compareOrder->pick_lat,
                    $compareOrder->pick_lng
                );
                
                // If within radius, add to this cluster
                if ($distance <= $radiusKm) {
                    $clusterOrders[] = $compareOrder;
                    $clusterIndices[] = $compareIndex;
                    $processed[] = $compareIndex;
                }
            }
            
            // Create cluster using the first order's coordinates as center
            $clusters[] = [
                'center_lat' => $order->pick_lat,  // Use exact coordinates of first order
                'center_lng' => $order->pick_lng,  // Use exact coordinates of first order
                'area_name' => $order->pick_name,  // Use area name of first order
                'order_count' => count($clusterOrders),
                'percentage' => round((count($clusterOrders) / $orders->count()) * 100, 1),
                'orders_in_cluster' => $clusterOrders // Optional: keep reference to all orders in cluster
            ];
        }
        
        return $clusters;
    }
    
    /**
     * Determine demand level based on order count
     */
    private function getDemandLevel($orderCount)
    {
        if ($orderCount >= 10) {
            return [
                'level' => 'very_high',
                'label' => 'Very High Demand',
                'color' => '#dc3545', // Red
                'icon' => '🔥'
            ];
        } elseif ($orderCount >= 7) {
            return [
                'level' => 'high',
                'label' => 'High Demand',
                'color' => '#fd7e14', // Orange
                'icon' => '⚡'
            ];
        } elseif ($orderCount >= 4) {
            return [
                'level' => 'medium',
                'label' => 'Medium Demand',
                'color' => '#ffc107', // Yellow
                'icon' => '📍'
            ];
        } else {
            return [
                'level' => 'low',
                'label' => 'Low Demand',
                'color' => '#28a745', // Green
                'icon' => '📌'
            ];
        }
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
}