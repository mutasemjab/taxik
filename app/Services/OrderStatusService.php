<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Carbon\Carbon;

class OrderStatusService
{
    /**
     * Record status change and return duration from previous status
     */
    public function recordStatusChange(Order $order, string $newStatus, $changedBy = null, $changedByType = null)
    {
        $now = now();
        
        // Get the last status change
        $lastStatusChange = OrderStatusHistory::where('order_id', $order->id)
            ->orderBy('changed_at', 'desc')
            ->first();
        
        // Calculate duration from last status
        $durationInMinutes = null;
        if ($lastStatusChange) {
            $durationInMinutes = $lastStatusChange->changed_at->diffInMinutes($now);
        }
        
        // Record new status
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'changed_at' => $now,
            'changed_by' => $changedBy,
            'changed_by_type' => $changedByType
        ]);
        
        // Special handling for 'arrived' status
        if ($newStatus === 'arrived') {
            $order->arrived_at = $now;
            $order->save();
        }
        
        return [
            'previous_status' => $lastStatusChange?->status,
            'duration_minutes' => $durationInMinutes,
            'changed_at' => $now
        ];
    }
    
    /**
     * Calculate waiting charges when moving from 'arrived' to 'started'
     * Gets configuration from the service
     */
    public function calculateWaitingCharges(Order $order)
    {
        if (!$order->arrived_at || !$order->service) {
            return [
                'waiting_minutes' => 0,
                'billable_minutes' => 0,
                'waiting_charges' => 0
            ];
        }
        
        $arrivedAt = Carbon::parse($order->arrived_at);
        $now = now();
        
        // Get configuration from service
        $freeMinutes = $order->service->free_waiting_minutes ?? 3;
        $chargePerMinute = $order->service->waiting_charge_per_minute ?? 0;
        
        $totalWaitingMinutes = $arrivedAt->diffInMinutes($now);
        
        // Calculate billable minutes (minutes after free period)
        $billableMinutes = max(0, $totalWaitingMinutes - $freeMinutes);
        $waitingCharges = $billableMinutes * $chargePerMinute;
        
        return [
            'total_waiting_minutes' => $totalWaitingMinutes,
            'free_minutes' => $freeMinutes,
            'billable_minutes' => $billableMinutes,
            'charge_per_minute' => $chargePerMinute,
            'waiting_charges' => round($waitingCharges, 2)
        ];
    }
    
    /**
     * Get status duration report for an order
     */
    public function getOrderStatusDurations(Order $order)
    {
        $histories = OrderStatusHistory::where('order_id', $order->id)
            ->orderBy('changed_at', 'asc')
            ->get();
        
        $durations = [];
        $previousHistory = null;
        
        foreach ($histories as $history) {
            if ($previousHistory) {
                $duration = $previousHistory->changed_at->diffInMinutes($history->changed_at);
                $durations[] = [
                    'status' => $previousHistory->status,
                    'started_at' => $previousHistory->changed_at->format('Y-m-d H:i:s'),
                    'ended_at' => $history->changed_at->format('Y-m-d H:i:s'),
                    'duration_minutes' => $duration
                ];
            }
            $previousHistory = $history;
        }
        
        // Add current status duration
        if ($previousHistory) {
            $durations[] = [
                'status' => $previousHistory->status,
                'started_at' => $previousHistory->changed_at->format('Y-m-d H:i:s'),
                'ended_at' => null,
                'duration_minutes' => $previousHistory->changed_at->diffInMinutes(now()),
                'is_current' => true
            ];
        }
        
        return $durations;
    }
    
    /**
     * Get driver performance report with average duration per status
     */
    public function getDriverPerformanceReport($driverId, $startDate = null, $endDate = null)
    {
        $query = OrderStatusHistory::select('order_status_histories.*')
            ->join('orders', 'orders.id', '=', 'order_status_histories.order_id')
            ->where('orders.driver_id', $driverId);
        
        if ($startDate) {
            $query->where('order_status_histories.changed_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('order_status_histories.changed_at', '<=', $endDate);
        }
        
        $histories = $query->orderBy('order_status_histories.order_id')
            ->orderBy('order_status_histories.changed_at')
            ->get();
        
        $stats = [];
        $previousHistory = null;
        
        foreach ($histories as $history) {
            if ($previousHistory && $previousHistory->order_id === $history->order_id) {
                $status = $previousHistory->status;
                $duration = $previousHistory->changed_at->diffInMinutes($history->changed_at);
                
                if (!isset($stats[$status])) {
                    $stats[$status] = [
                        'total_duration_minutes' => 0,
                        'count' => 0,
                        'avg_duration_minutes' => 0,
                        'min_duration_minutes' => PHP_INT_MAX,
                        'max_duration_minutes' => 0
                    ];
                }
                
                $stats[$status]['total_duration_minutes'] += $duration;
                $stats[$status]['count']++;
                $stats[$status]['min_duration_minutes'] = min($stats[$status]['min_duration_minutes'], $duration);
                $stats[$status]['max_duration_minutes'] = max($stats[$status]['max_duration_minutes'], $duration);
                $stats[$status]['avg_duration_minutes'] = round($stats[$status]['total_duration_minutes'] / $stats[$status]['count'], 2);
            }
            $previousHistory = $history;
        }
        
        return $stats;
    }
}