<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverAcceptanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:order-table');
    }

    /**
     * صفحة تقرير السائقين الذين وافقوا على الطلبات
     * تعرض السائقين الذين قبلوا طلبات (حالتها تحولت من pending → accepted/driver_accepted)
     */
    public function index(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)->startOfDay()
            : Carbon::today()->startOfDay();

        $dateEnd = $date->copy()->endOfDay();

        // جلب الطلبات التي تم قبولها من قِبل سائقين في التاريخ المحدد
        // الطلبات التي لها driver_id (أي وافق عليها سائق) وتاريخ updated_at في اليوم المطلوب
        // وحالتها ليست pending (أي تم قبولها من قبل سائق)
        $query = DB::table('orders')
            ->join('drivers', 'orders.driver_id', '=', 'drivers.id')
            ->whereNotNull('orders.driver_id')
            ->whereNotIn('orders.status', ['pending', 'cancel_cron_job'])
            ->whereBetween('orders.updated_at', [$date, $dateEnd])
            ->select(
                'drivers.id as driver_id',
                'drivers.name as driver_name',
                'drivers.phone as driver_phone',
                'drivers.photo as driver_photo',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('MIN(orders.updated_at) as first_acceptance'),
                DB::raw('MAX(orders.updated_at) as last_acceptance'),
                DB::raw('GROUP_CONCAT(orders.id ORDER BY orders.updated_at SEPARATOR ",") as order_ids'),
                DB::raw('GROUP_CONCAT(orders.status ORDER BY orders.updated_at SEPARATOR ",") as order_statuses'),
                DB::raw('SUM(CASE WHEN orders.status IN ("user_cancel_order","driver_cancel_order") THEN 1 ELSE 0 END) as cancelled_count'),
                DB::raw('SUM(CASE WHEN orders.status = "completed" THEN 1 ELSE 0 END) as completed_count')
            )
            ->groupBy('drivers.id', 'drivers.name', 'drivers.phone', 'drivers.photo')
            ->orderByDesc('orders_count');

        if ($request->filled('driver_id')) {
            $query->where('orders.driver_id', $request->driver_id);
        }

        $reportData = $query->get();

        // جلب قائمة السائقين للفلتر
        $drivers = DB::table('drivers')->select('id', 'name', 'phone')->orderBy('name')->get();

        return view('admin.reports.driver-acceptance', compact('reportData', 'date', 'drivers'));
    }
}
