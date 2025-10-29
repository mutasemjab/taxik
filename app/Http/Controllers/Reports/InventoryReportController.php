<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\NoteVoucher;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Shop;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $shops = Shop::all();
        $shopId = $request->input('shop_id');
        $toDate = $request->input('to_date', date('Y-m-d')); // Default to today's date if not provided

        $reportData = [];

        if ($shopId) {
            // Fetch note vouchers filtered by shop and date
            $noteVouchers = NoteVoucher::where('shop_id', $shopId)
                ->where('date_note_voucher', '<=', $toDate)
                ->with(['voucherProducts'])
                ->get();

            $productQuantities = [];
            $productCosts = [];

            foreach ($noteVouchers as $noteVoucher) {
                foreach ($noteVoucher->voucherProducts as $voucherProduct) {
                    $productId = $voucherProduct->id;
                    $unitId = $voucherProduct->pivot->unit_id;
                    $quantity = $voucherProduct->pivot->quantity;
                    $purchasingPrice = $voucherProduct->pivot->purchasing_price;
                    $noteVoucherTypeId = $noteVoucher->note_voucher_type_id;

                    // Convert quantity to the basic unit if necessary
                    $product = Product::find($productId);
                    if ($unitId != $product->unit_id) {
                        $productUnit = $product->units()->where('unit_id', $unitId)->first();
                        if ($productUnit) {
                            $quantity *= $productUnit->pivot->releation;
                        }
                    }

                    if (!isset($productQuantities[$productId])) {
                        $productQuantities[$productId] = 0;
                        $productCosts[$productId] = 0;
                    }

                    // Adjust quantity and cost based on note_voucher_type_id
                    if ($noteVoucherTypeId == 1) { // 'In' without purchasing price
                        $productQuantities[$productId] += $quantity;
                    } elseif ($noteVoucherTypeId == 2) { // 'Out'
                        $productQuantities[$productId] -= $quantity;
                    } elseif ($noteVoucherTypeId == 3) { // 'In' with purchasing price
                        $productQuantities[$productId] += $quantity;
                        if ($purchasingPrice) {
                            $productCosts[$productId] = ($productCosts[$productId] * ($productQuantities[$productId] - $quantity) + $quantity * $purchasingPrice) / $productQuantities[$productId];
                        }
                    }
                }
            }

            // Fetch product details and prepare report data
            foreach ($productQuantities as $productId => $quantity) {
                $product = Product::find($productId);
                $unit = $product->unit;
                $weightedAverageCost = isset($productCosts[$productId]) ? $productCosts[$productId] : 0;

                $reportData[] = [
                    'product_name' => $product->name_ar ?? 'N/A',
                    'quantity' => $quantity,
                    'unit' => $unit->name_ar ?? 'N/A',
                    'weighted_average_cost' => $weightedAverageCost,
                ];
            }
        }

        return view('reports.inventory_report', compact('shops', 'reportData', 'shopId', 'toDate'));
    }


}
