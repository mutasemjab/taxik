<?php

namespace App\Http\Controllers\Reports;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderStatusReportController extends Controller
{
    /**
     * Display order status history report
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'driver', 'service']);
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get filter options
        $drivers = \App\Models\Driver::select('id', 'name')->get();
        $users = \App\Models\User::select('id', 'name')->get();
        $services = \App\Models\Service::select('id', 'name_en', 'name_ar')->get();
        
        return view('reports.order-status-history', compact('orders', 'drivers', 'users', 'services'));
    }
    
    /**
     * Display detailed status history for a specific order
     */
    public function show($orderId)
    {
        $order = Order::with(['user', 'driver', 'service'])->findOrFail($orderId);
        
        // Get status history with durations
        $statusHistories = OrderStatusHistory::where('order_id', $orderId)
            ->orderBy('changed_at', 'asc')
            ->get();
        
        // Calculate durations between each status
        $historyWithDurations = [];
        $previousHistory = null;
        $totalDuration = 0;
        
        foreach ($statusHistories as $index => $history) {
            $duration = null;
            $durationFormatted = null;
            
            if ($previousHistory) {
                $startTime = Carbon::parse($previousHistory->changed_at);
                $endTime = Carbon::parse($history->changed_at);
                $duration = $startTime->diffInMinutes($endTime);
                $durationFormatted = $this->formatDuration($duration);
                $totalDuration += $duration;
            }
            
            $historyWithDurations[] = [
                'status' => $previousHistory ? $previousHistory->status : null,
                'started_at' => $previousHistory ? $previousHistory->changed_at : null,
                'ended_at' => $history->changed_at,
                'next_status' => $history->status,
                'duration_minutes' => $duration,
                'duration_formatted' => $durationFormatted,
                'changed_by' => $previousHistory ? $previousHistory->changed_by : null,
                'changed_by_type' => $previousHistory ? $previousHistory->changed_by_type : null,
            ];
            
            $previousHistory = $history;
        }
        
        // Add current status duration (if order is not completed/cancelled)
        if ($previousHistory && !in_array($order->status->value, ['completed', 'user_cancel_order', 'driver_cancel_order'])) {
            $startTime = Carbon::parse($previousHistory->changed_at);
            $endTime = now();
            $duration = $startTime->diffInMinutes($endTime);
            $totalDuration += $duration;
            
            $historyWithDurations[] = [
                'status' => $previousHistory->status,
                'started_at' => $previousHistory->changed_at,
                'ended_at' => null,
                'next_status' => null,
                'duration_minutes' => $duration,
                'duration_formatted' => $this->formatDuration($duration),
                'changed_by' => $previousHistory->changed_by,
                'changed_by_type' => $previousHistory->changed_by_type,
                'is_current' => true,
            ];
        }
        
        $totalDurationFormatted = $this->formatDuration($totalDuration);
        
        return view('reports.order-status-detail', compact(
            'order',
            'historyWithDurations',
            'totalDuration',
            'totalDurationFormatted'
        ));
    }
    
    /**
     * Export report to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'driver', 'service']);
        
        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        $orders = $query->get();
        
        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Order ID',
            'User',
            'Driver',
            'Service',
            'Status',
            'Total Duration',
            'Created At',
            'Completed At'
        ];
        
        foreach ($orders as $order) {
            $totalDuration = $this->calculateOrderTotalDuration($order->id);
            
            $csvData[] = [
                $order->id,
                $order->user ? $order->user->name : 'N/A',
                $order->driver ? $order->driver->name : 'N/A',
                $order->service ? $order->service->name_en : 'N/A',
                $order->getStatusText(),
                $this->formatDuration($totalDuration),
                $order->created_at->format('Y-m-d H:i:s'),
                $order->status->value == 'completed' ? $order->updated_at->format('Y-m-d H:i:s') : 'N/A'
            ];
        }
        
        $fileName = 'order-status-report-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Calculate total duration for an order
     */
    private function calculateOrderTotalDuration($orderId)
    {
        $histories = OrderStatusHistory::where('order_id', $orderId)
            ->orderBy('changed_at', 'asc')
            ->get();
        
        if ($histories->isEmpty()) {
            return 0;
        }
        
        $firstChange = $histories->first();
        $lastChange = $histories->last();
        
        $order = Order::find($orderId);
        
        // If order is completed or cancelled, use last status change time
        if (in_array($order->status->value, ['completed', 'user_cancel_order', 'driver_cancel_order'])) {
            return Carbon::parse($firstChange->changed_at)->diffInMinutes(Carbon::parse($lastChange->changed_at));
        }
        
        // Otherwise, calculate until now
        return Carbon::parse($firstChange->changed_at)->diffInMinutes(now());
    }
    
    /**
     * Format duration in minutes to human-readable format
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 1) {
            return '< 1 min';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $mins . 'm';
        }
        
        return $mins . 'm';
    }
}
