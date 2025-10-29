<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Models\NoteVoucher;
use App\Models\VoucherProduct;
use App\Models\OrderProduct;
use Carbon\Carbon;

class ProductReportController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::all();
        $products = Product::all();
        $shopId = $request->input('shop_id');
        $productId = $request->input('product_id');
        $fromDate = $request->input('from_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $reportData = [];

        if ($shopId && $productId) {
            // Get note vouchers
            $noteVouchers = NoteVoucher::where('shop_id', $shopId)
                ->whereHas('voucherProducts', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                })
                ->whereBetween('date_note_voucher', [$fromDate, $toDate])
                ->get();

            // Get voucher products
            $voucherProducts = VoucherProduct::where('product_id', $productId)
                ->whereIn('note_voucher_id', $noteVouchers->pluck('id'))
                ->get();

            // Get orders
            $orders = Order::where('shop_id', $shopId)->where('order_status',4)
                ->whereHas('products', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                })
                ->whereBetween('date', [$fromDate, $toDate])
                ->get();

            // Get order products
            $orderProducts = OrderProduct::where('product_id', $productId)
                ->whereIn('order_id', $orders->pluck('id'))
                ->get();

            // Get product details
            $product = Product::find($productId);

            // Collect user data who ordered the product
            $ordersWithUser = $orders->load('user');

            $reportData = [
                'noteVouchers' => $noteVouchers,
                'voucherProducts' => $voucherProducts,
                'orders' => $ordersWithUser,
                'orderProducts' => $orderProducts,
                'availableQuantityForUser' => $product->available_quantity_for_user,
                'availableQuantityForWholeSale' => $product->available_quantity_for_wholeSale,
            ];
        }

        return view('reports.product_move', compact('shops', 'products', 'reportData', 'shopId', 'fromDate', 'toDate', 'productId'));
    }
}
