<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Coupon;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::with('service')->get();
        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::all();
        return view('admin.coupons.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:coupons',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'discount' => 'required|numeric|min:0',
            'activate' => 'required|in:1,2',
            'discount_type' => 'required|in:1,2',
            'coupon_type' => 'required|in:1,2,3',
            'service_id' => 'nullable|required_if:coupon_type,3|exists:services,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('coupons.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Set service_id to null if not applicable
        $couponData = $request->all();
        if ($request->coupon_type != 3) {
            $couponData['service_id'] = null;
        }

        Coupon::create($couponData);

        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Created_Successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $coupon = Coupon::with('service')->findOrFail($id);
        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $services = Service::all();
        return view('admin.coupons.edit', compact('coupon', 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:coupons,code,' . $id,
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'discount' => 'required|numeric|min:0',
            'activate' => 'required|in:1,2',
            'discount_type' => 'required|in:1,2',
            'coupon_type' => 'required|in:1,2,3',
            'service_id' => 'nullable|required_if:coupon_type,3|exists:services,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('coupons.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        // Set service_id to null if not applicable
        $couponData = $request->all();
        if ($request->coupon_type != 3) {
            $couponData['service_id'] = null;
        }

        $coupon->update($couponData);

        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Updated_Successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Deleted_Successfully'));
    }

    /**
     * Toggle activation status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleActivation($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        // Toggle activation: 1 -> 2, 2 -> 1
        $coupon->activate = ($coupon->activate == 1) ? 2 : 1;
        $coupon->save();
        
        $status = ($coupon->activate == 1) ? 'activated' : 'deactivated';
        
        return redirect()
            ->route('coupons.index')
            ->with('success', __('messages.Coupon_Toggle_Success', ['status' => $status]));
    }
}