<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Models\OrderProduct;
use Carbon\Carbon;



class TaxReportController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::all();
        $shopId = $request->input('shop_id');
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $netTotal = 0;
        $netTaxTotal = 0;

        $reportData = [];

        if ($shopId) {
            // Get orders within the date range for the selected shop
            $orders = Order::where('shop_id', $shopId)
                ->whereBetween('date', [$fromDate, $toDate])
                ->get();

            // Get unique tax percentages from order products in orders
            $taxes = OrderProduct::whereIn('order_id', $orders->pluck('id'))
                ->distinct()
                ->pluck('tax_percentage');

            foreach ($taxes as $tax) {
                $salesOrders = OrderProduct::whereIn('order_id', $orders->pluck('id'))
                    ->where('tax_percentage', $tax)
                    ->whereHas('order', function($query) {
                        $query->where('order_type', 1); // 1 for Sell
                    })
                    ->get();

                $refundOrders = OrderProduct::whereIn('order_id', $orders->pluck('id'))
                    ->where('tax_percentage', $tax)
                    ->whereHas('order', function($query) {
                        $query->where('order_type', 2); // 2 for Refund
                    })
                    ->get();

                $salesTotal = $salesOrders->sum('total_price_after_tax');
                $refundTotal = $refundOrders->sum('total_price_after_tax');

                $salesTax = $salesOrders->sum('tax_value');
                $refundTax = $refundOrders->sum('tax_value');

                $netTotalForTax = $salesTotal - $refundTotal;
                $netTaxForTax = $salesTax - $refundTax;

                $netTotal += $netTotalForTax;
                $netTaxTotal += $netTaxForTax;

                $reportData[] = [
                    'tax' => $tax,
                    'sales' => $salesTotal,
                    'tax_amount_sales' => $salesTax,
                    'refund' => $refundTotal,
                    'tax_amount_refund' => $refundTax,
                    'net' => $netTotalForTax,
                    'net_tax' => $netTaxForTax,
                ];
            }
        }

        return view('reports.tax_report', compact('shops', 'reportData', 'netTotal', 'netTaxTotal', 'shopId', 'fromDate', 'toDate'));
    }
}
