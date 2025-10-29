<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Shop;
use App\Models\Representative;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::all();
        $representatives = Representative::all();
        $shopId = $request->input('shop_id');
        $representativeId = $request->input('representative_id');
        $orderStatus = $request->input('order_status');
        $toDate = $request->input('to_date', date('Y-m-d')); // Default to today's date if not provided
        $userType = $request->input('user_type'); // Added user_type

        $reportData = [];

        if ($shopId) {
            $query = Order::where('shop_id', $shopId)
                ->where('date', '<=', $toDate)
                ->with(['user', 'shop', 'user.representative']);

            if ($representativeId) {
                $query->whereHas('user.representative', function($q) use ($representativeId) {
                    $q->where('id', $representativeId);
                });
            }

            if ($orderStatus) {
                $query->where('order_status', $orderStatus);
            }

            if ($userType) {
                $query->whereHas('user', function($q) use ($userType) {
                    $q->where('user_type', $userType);
                });
            }

            $orders = $query->get();

            foreach ($orders as $order) {
                $reportData[] = [
                    'order_id' => $order->id ?? 'N/A',
                    'total_prices' => $order->total_prices,
                    'order_status' => $this->getOrderStatusText($order->order_status),
                ];
            }
        }

        return view('reports.order_report', compact('shops','representatives', 'reportData', 'shopId', 'toDate', 'representativeId', 'orderStatus', 'userType'));
    }

    private function getOrderStatusText($status)
    {
        $statusTexts = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'OnTheWay',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Refund',
        ];

        return $statusTexts[$status] ?? 'Unknown';
    }
}
