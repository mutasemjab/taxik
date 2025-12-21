<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:dashboard-view')->only('index', 'getStatsByPeriod');
    // }

    public function index()
    {
        // Get current date for calculations
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;

        // Basic counts
        $usersCount = User::count();
        $driversCount = Driver::count();

        // Today's statistics
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $completedOrdersToday = Order::whereDate('updated_at', $today)
            ->where('status', 'completed')->count();
        $pendingOrdersToday = Order::whereDate('created_at', $today)
            ->where('status', 'pending')->count();
        $canceledOrdersToday = Order::whereDate('updated_at', $today)
            ->whereIn('status', ['user_cancel_order', 'driver_cancel_order'])->count();

        // Yesterday's orders for comparison
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();

        // New users today
        $newUsersToday = User::whereDate('created_at', $today)->count();

        // Active drivers (drivers who completed at least one order today)
        $activeDriversToday = Order::whereDate('updated_at', $today)
            ->where('status', 'completed')
            ->distinct('driver_id')
            ->count('driver_id');

        // Currently online drivers (assuming drivers with status = 1 are online)
        $activeDriversNow = Driver::where('status', 1)->where('activate', 1)->count();

        // Earnings calculations
        $todayEarnings = $this->calculateTodayEarnings();
        $yesterdayEarnings = $this->calculateYesterdayEarnings();
        $monthlyEarnings = $this->calculateMonthlyEarnings($thisMonth, $thisYear);
        $totalEarnings = $this->calculateTotalEarnings();

        // Admin commission and driver earnings for today
        $adminCommissionToday = Order::whereDate('updated_at', $today)
            ->where('status', 'completed')
            ->sum('commision_of_admin');

        $driversEarningsToday = Order::whereDate('updated_at', $today)
            ->where('status', 'completed')
            ->sum('net_price_for_driver');

        // Monthly orders
        $monthlyOrders = Order::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();

        // Completion rate
        $totalOrdersThisMonth = Order::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();
        $completedOrdersThisMonth = Order::whereMonth('updated_at', $thisMonth)
            ->whereYear('updated_at', $thisYear)
            ->where('status', 'completed')
            ->count();
        $completionRate = $totalOrdersThisMonth > 0 ? 
            round(($completedOrdersThisMonth / $totalOrdersThisMonth) * 100, 1) : 0;


        // Average order completion time (in minutes)
        $averageOrderTime = $this->calculateAverageOrderTime();

        // Average order value for this month
        $averageOrderValue = $monthlyOrders > 0 ? 
            ($monthlyEarnings / $monthlyOrders) : 0;

        // Growth calculations
        $earningsGrowth = $this->calculateGrowthPercentage($todayEarnings, $yesterdayEarnings);
        $ordersGrowth = $this->calculateGrowthPercentage($todayOrders, $yesterdayOrders);
        $usersGrowth = $this->calculateUsersGrowthThisMonth();
        $driversGrowth = $this->calculateDriversGrowthThisMonth();

        return view('admin.dashboard', compact(
            'usersCount',
            'driversCount',
            'todayOrders',
            'completedOrdersToday',
            'pendingOrdersToday',
            'canceledOrdersToday',
            'newUsersToday',
            'activeDriversToday',
            'activeDriversNow',
            'todayEarnings',
            'monthlyEarnings',
            'totalEarnings',
            'adminCommissionToday',
            'driversEarningsToday',
            'monthlyOrders',
            'completionRate',
            'averageOrderTime',
            'averageOrderValue',
            'earningsGrowth',
            'ordersGrowth',
            'usersGrowth',
            'driversGrowth'
        ));
    }

    /**
     * Calculate today's total earnings from completed orders
     */
    private function calculateTodayEarnings()
    {
        return Order::whereDate('updated_at', Carbon::today())
            ->where('status', 'completed')
            ->sum('total_price_after_discount');
    }

    /**
     * Calculate yesterday's total earnings
     */
    private function calculateYesterdayEarnings()
    {
        return Order::whereDate('updated_at', Carbon::yesterday())
            ->where('status', 'completed')
            ->sum('total_price_after_discount');
    }

    /**
     * Calculate monthly earnings
     */
    private function calculateMonthlyEarnings($month, $year)
    {
        return Order::whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->where('status', 'completed')
            ->sum('total_price_after_discount');
    }

    /**
     * Calculate total earnings from all completed orders
     */
    private function calculateTotalEarnings()
    {
        return Order::where('status', 'completed')
            ->sum('total_price_after_discount');
    }

   

    /**
     * Calculate average order completion time
     */
    private function calculateAverageOrderTime()
    {
        $completedOrders = Order::where('status', 'completed')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->whereDate('updated_at', '>=', Carbon::today()->subDays(30))
            ->get(['created_at', 'updated_at']);

        if ($completedOrders->count() == 0) {
            return 25; // Default average time
        }

        $totalMinutes = 0;
        foreach ($completedOrders as $order) {
            $totalMinutes += $order->created_at->diffInMinutes($order->updated_at);
        }

        return round($totalMinutes / $completedOrders->count());
    }

    /**
     * Calculate growth percentage between two values
     */
    private function calculateGrowthPercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Calculate users growth for this month compared to last month
     */
    private function calculateUsersGrowthThisMonth()
    {
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        $usersThisMonth = User::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();

        $usersLastMonth = User::whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->count();

        return $this->calculateGrowthPercentage($usersThisMonth, $usersLastMonth);
    }

    /**
     * Calculate drivers growth for this month compared to last month
     */
    private function calculateDriversGrowthThisMonth()
    {
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        $driversThisMonth = Driver::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();

        $driversLastMonth = Driver::whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastMonthYear)
            ->count();

        return $this->calculateGrowthPercentage($driversThisMonth, $driversLastMonth);
    }

    /**
     * Get statistics for a specific period (for AJAX requests)
     */
    public function getStatsByPeriod(Request $request)
    {
        $period = $request->get('period', 'today');
        
        switch ($period) {
            case 'today':
                return $this->getTodayStats();
            case 'week':
                return $this->getWeekStats();
            case 'month':
                return $this->getMonthStats();
            case 'year':
                return $this->getYearStats();
            default:
                return $this->getTodayStats();
        }
    }

    private function getTodayStats()
    {
        // Return today's statistics in JSON format for AJAX
        return response()->json([
            'orders' => Order::whereDate('created_at', Carbon::today())->count(),
            'earnings' => $this->calculateTodayEarnings(),
            'users' => User::whereDate('created_at', Carbon::today())->count(),
            'drivers' => Driver::whereDate('created_at', Carbon::today())->count(),
        ]);
    }

    private function getWeekStats()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        return response()->json([
            'orders' => Order::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'earnings' => Order::whereBetween('updated_at', [$weekStart, $weekEnd])
                ->where('status', 'completed')
                ->sum('total_price_after_discount'),
            'users' => User::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'drivers' => Driver::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
        ]);
    }

    private function getMonthStats()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        return response()->json([
            'orders' => Order::whereMonth('created_at', $month)->whereYear('created_at', $year)->count(),
            'earnings' => $this->calculateMonthlyEarnings($month, $year),
            'users' => User::whereMonth('created_at', $month)->whereYear('created_at', $year)->count(),
            'drivers' => Driver::whereMonth('created_at', $month)->whereYear('created_at', $year)->count(),
        ]);
    }

    private function getYearStats()
    {
        $year = Carbon::now()->year;

        return response()->json([
            'orders' => Order::whereYear('created_at', $year)->count(),
            'earnings' => Order::whereYear('updated_at', $year)
                ->where('status', 'completed')
                ->sum('total_price_after_discount'),
            'users' => User::whereYear('created_at', $year)->count(),
            'drivers' => Driver::whereYear('created_at', $year)->count(),
        ]);
    }
}
