<?php

namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Enums\OrderStatus; 


class DriverStatisticsController extends Controller
{
    /**
     * Get monthly statistics for driver
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyStatistics(Request $request)
    {
        try {
            $driver = Auth::guard('driver-api')->user();
            
            // Validate input
            $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'nullable|integer|min:2020',
            ]);
            
            $month = $request->month;
            $year = $request->year ?? date('Y');
            
            // Get start and end dates for the month
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
            
            // Get all orders for this driver in this month
            $orders = Order::where('driver_id', $driver->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
            
            // 🔥 استخدم الـ Enum مباشرة بدون ->value
            $completedOrders = $orders->where('status', OrderStatus::Delivered);
            $completedCount = $completedOrders->count();
            
            // Calculate cancelled orders by driver
            $cancelledByDriverOrders = $orders->where('status', OrderStatus::DriverCancelOrder);
            $cancelledCount = $cancelledByDriverOrders->count();
            
            // Calculate total earnings (net price for driver from completed orders)
            $totalEarnings = $completedOrders->sum('net_price_for_driver');
            
            // Get day-by-day breakdown for chart
            $dailyData = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $dayStart = $currentDate->copy()->startOfDay();
                $dayEnd = $currentDate->copy()->endOfDay();
                
                $dayOrders = $orders->whereBetween('created_at', [$dayStart, $dayEnd]);
                
                // 🔥 استخدم الـ Enum مباشرة
                $dayCompleted = $dayOrders->where('status', OrderStatus::Delivered);
                $dayCancelled = $dayOrders->where('status', OrderStatus::DriverCancelOrder);
                $dayEarnings = $dayCompleted->sum('net_price_for_driver');
                
                $dailyData[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day' => $currentDate->format('d'),
                    'day_name' => $currentDate->locale('ar')->translatedFormat('l'),
                    'day_name_en' => $currentDate->format('l'),
                    'completed_orders' => $dayCompleted->count(),
                    'cancelled_orders' => $dayCancelled->count(),
                    'earnings' => (float) $dayEarnings,
                ];
                
                $currentDate->addDay();
            }
            
            // Get completed orders details
            $completedOrdersList = $completedOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'date' => $order->created_at->format('Y-m-d H:i:s'),
                    'pick_name' => $order->pick_name,
                    'drop_name' => $order->drop_name,
                    'total_price' => (float) $order->total_price_after_discount,
                    'net_price_for_driver' => (float) $order->net_price_for_driver,
                    'commission' => (float) $order->commision_of_admin,
                    'payment_method' => $order->payment_method,
                ];
            })->values();
            
            // Get cancelled orders details
            $cancelledOrdersList = $cancelledByDriverOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'date' => $order->created_at->format('Y-m-d H:i:s'),
                    'pick_name' => $order->pick_name,
                    'drop_name' => $order->drop_name,
                    'reason_for_cancel' => $order->reason_for_cancel,
                ];
            })->values();
            
            return response()->json([
                'status' => true,
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'month_name' => Carbon::create($year, $month, 1)->locale('ar')->translatedFormat('F'),
                    'month_name_en' => Carbon::create($year, $month, 1)->format('F'),
                    
                    // Summary statistics
                    'summary' => [
                        'total_completed_orders' => $completedCount,
                        'total_cancelled_orders' => $cancelledCount,
                        'total_earnings' => (float) $totalEarnings,
                        'average_earning_per_order' => $completedCount > 0 ? (float) ($totalEarnings / $completedCount) : 0,
                    ],
                    
                    // Daily breakdown for chart
                    'daily_statistics' => $dailyData,
                    
                    // Detailed lists
                    'completed_orders' => $completedOrdersList,
                    'cancelled_orders' => $cancelledOrdersList,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching monthly statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available months with data for driver
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableMonths()
    {
        try {
            $driver = Auth::guard('driver-api')->user();
            
            // Get all months where driver has orders
            $months = Order::where('driver_id', $driver->id)
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as total_orders')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function ($item) {
                    $date = Carbon::create($item->year, $item->month, 1);
                    return [
                        'year' => (int) $item->year,
                        'month' => (int) $item->month,
                        'month_name' => $date->locale('ar')->translatedFormat('F Y'),
                        'month_name_en' => $date->format('F Y'),
                        'total_orders' => (int) $item->total_orders,
                    ];
                });
            
            return response()->json([
                'status' => true,
                'data' => [
                    'available_months' => $months
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching available months',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}