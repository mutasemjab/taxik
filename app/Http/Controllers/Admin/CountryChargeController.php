<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\CountryCharge;
use App\Models\ChargeData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CountryChargeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:countrCharge-table', ['only' => ['index']]);
        $this->middleware('permission:countrCharge-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:countrCharge-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:countrCharge-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $countryCharges = CountryCharge::with('chargeData')->latest()->paginate(10);
        return view('admin.country-charges.index', compact('countryCharges'));
    }

    public function create()
    {
        return view('admin.country-charges.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_name' => 'required|string|max:255',
            'charge_data' => 'required|array|min:1',
            'charge_data.*.name' => 'required|string|max:255',
            'charge_data.*.phone' => 'required|string|max:20',
            'charge_data.*.cliq_name' => 'required|string|max:255',
        ], [
            'charge_data.required' => __('messages.At least one charge data is required'),
            'charge_data.min' => __('messages.At least one charge data is required'),
            'charge_data.*.name.required' => __('messages.Name is required for all charge data'),
            'charge_data.*.phone.required' => __('messages.Phone is required for all charge data'),
            'charge_data.*.cliq_name.required' => __('messages.Cliq name is required for all charge data'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create Country Charge
            $countryCharge = CountryCharge::create([
                'name' => $request->country_name,
            ]);

            // Create Charge Data
            foreach ($request->charge_data as $data) {
                ChargeData::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'cliq_name' => $data['cliq_name'],
                    'country_charge_id' => $countryCharge->id,
                ]);
            }

            DB::commit();

            return redirect()->route('country-charges.index')
                ->with('success', __('messages.country_charge_added_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', __('messages.An error occurred while saving'))
                ->withInput();
        }
    }

    public function edit(CountryCharge $countryCharge)
    {
        $countryCharge->load('chargeData');
        return view('admin.country-charges.edit', compact('countryCharge'));
    }

    public function update(Request $request, CountryCharge $countryCharge)
    {
        $validator = Validator::make($request->all(), [
            'country_name' => 'required|string|max:255',
            'charge_data' => 'required|array|min:1',
            'charge_data.*.name' => 'required|string|max:255',
            'charge_data.*.phone' => 'required|string|max:20',
            'charge_data.*.cliq_name' => 'required|string|max:255',
        ], [
            'charge_data.required' => __('messages.At least one charge data is required'),
            'charge_data.min' => __('messages.At least one charge data is required'),
            'charge_data.*.name.required' => __('messages.Name is required for all charge data'),
            'charge_data.*.phone.required' => __('messages.Phone is required for all charge data'),
            'charge_data.*.cliq_name.required' => __('messages.Cliq name is required for all charge data'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update Country Charge
            $countryCharge->update([
                'name' => $request->country_name,
            ]);

            // Delete existing charge data
            $countryCharge->chargeData()->delete();

            // Create new charge data
            foreach ($request->charge_data as $data) {
                ChargeData::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'cliq_name' => $data['cliq_name'],
                    'country_charge_id' => $countryCharge->id,
                ]);
            }

            DB::commit();

            return redirect()->route('country-charges.index')
                ->with('success', __('messages.country_charge_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', __('messages.An error occurred while updating'))
                ->withInput();
        }
    }

    public function destroy(CountryCharge $countryCharge)
    {
        try {
            $countryCharge->delete();
            return redirect()->route('country-charges.index')
                ->with('success', __('messages.country_charge_deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('messages.An error occurred while deleting'));
        }
    }
}