<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Service;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class CouponController extends Controller
{
    use Responses;

    /**
     * Validate a coupon code for a service
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function validateCoupon(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // Get the coupon
        $couponCode = strtoupper($request->code);
        $coupon = Coupon::where('code', $couponCode)
            ->where('activate', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with('service')
            ->first();

        if (!$coupon) {
            return $this->error_response('Invalid or expired coupon code', null);
        }

        // Check if user has already used this coupon
        $hasUsedCoupon = DB::table('user_coupons')
            ->where('user_id', $user->id)
            ->where('coupon_id', $coupon->id)
            ->exists();

        if ($hasUsedCoupon) {
            return $this->error_response('You have already used this coupon', null);
        }

        // Check coupon usage limit (if number_of_used is not null)
        if (!is_null($coupon->number_of_used)) {
            $currentUsageCount = DB::table('user_coupons')
                ->where('coupon_id', $coupon->id)
                ->count();

            if ($currentUsageCount >= $coupon->number_of_used) {
                return $this->error_response('This coupon has reached its maximum usage limit', null);
            }
        }

        // Check for service-specific coupon
        if ($coupon->coupon_type == 3 && $coupon->service_id != $request->service_id) {
            return $this->error_response('This coupon is only valid for ' . $coupon->service->name . ' service', null);
        }

        // Check for first ride coupon
        if ($coupon->coupon_type == 2) {
            $hasCompletedRides = Order::where('user_id', $user->id)
                ->where('status', 5) // Completed status
                ->exists();

            if ($hasCompletedRides) {
                return $this->error_response('This coupon is only valid for your first ride', null);
            }
        }

        $responseData = [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'title' => $coupon->title,
                'discount_type' => $coupon->discount_type,
                'discount_type_text' => $coupon->getDiscountTypeText(),
                'discount' => $coupon->discount,
                'formatted_discount' => $coupon->getFormattedDiscount()
            ],
        ];

        return $this->success_response('Coupon applied successfully', $responseData);
    }
}
