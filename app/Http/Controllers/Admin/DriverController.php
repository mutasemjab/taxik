<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverBan;
use App\Models\Option;
use App\Models\Service;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $drivers = Driver::with('options')->get();

        return view('admin.drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $options = Option::all();

        return view('admin.drivers.create', compact('options'));
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
            'name' => 'required|string|max:255',
            'country_code' => 'required',
            'phone' => 'required|string|unique:drivers',
            'email' => 'nullable|email|unique:drivers',
            'sos_phone' => 'nullable|string',
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|exists:options,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Services validation
            'primary_service_id' => 'required|exists:services,id',
            'optional_service_ids' => 'nullable|array',
            'optional_service_ids.*' => 'exists:services,id',

            // Car details
            'photo_of_car' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'model' => 'nullable|string|max:255',
            'production_year' => 'nullable|string|max:4',
            'color' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',

            // Documents
            'driving_license_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'driving_license_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'car_license_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'car_license_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'no_criminal_record' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('drivers.create')
                ->withErrors($validator)
                ->withInput();
        }

        $driverData = $request->except([
            'photo',
            'photo_of_car',
            'driving_license_front',
            'driving_license_back',
            'car_license_front',
            'car_license_back',
            'no_criminal_record',
            'password',
            'primary_service_id',
            'optional_service_ids'
        ]);

        // Set default values
        $driverData['balance'] = $request->has('balance') ? $request->balance : 0;
        $driverData['activate'] = $request->has('activate') ? $request->activate : 1;

        // Handle all image uploads
        $imageFields = [
            'photo',
            'photo_of_car',
            'driving_license_front',
            'driving_license_back',
            'car_license_front',
            'car_license_back'
        ];

        foreach ($imageFields as $field) {
            if ($request->has($field)) {
                $driverData[$field] = uploadImage('assets/admin/uploads', $request->$field);
            }
        }

        $driver = Driver::create($driverData);

        // Attach options to the driver
        if ($request->has('option_ids') && is_array($request->option_ids)) {
            $driver->options()->attach($request->option_ids);
        }

        // Handle services
        $servicesToAttach = [];

        // Add primary service
        if ($request->has('primary_service_id')) {
            $servicesToAttach[$request->primary_service_id] = [
                'service_type' => 1,
                'status' => 1
            ];
        }

        // Add optional services
        if ($request->has('optional_service_ids') && is_array($request->optional_service_ids)) {
            foreach ($request->optional_service_ids as $serviceId) {
                if ($serviceId != $request->primary_service_id) {
                    $servicesToAttach[$serviceId] = [
                        'service_type' => 2,
                        'status' => 1
                    ];
                }
            }
        }

        // Attach services
        $driver->services()->attach($servicesToAttach);

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $driver = Driver::with('options')->findOrFail($id);

        return view('admin.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $driver = Driver::with('services')->findOrFail($id);
        $options = Option::all();
        $allServices = Service::where('activate', 1)->get();

        // Get services already assigned to driver
        $driverServiceIds = $driver->services->pluck('id')->toArray();

        return view('admin.drivers.edit', compact('driver', 'options', 'allServices', 'driverServiceIds'));
    }

    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:drivers,phone,' . $id,
            'email' => 'nullable|email|unique:drivers,email,' . $id,
            'sos_phone' => 'nullable|string',
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|exists:options,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Services validation
            'primary_service_id' => 'required|exists:services,id',
            'optional_service_ids' => 'nullable|array',
            'optional_service_ids.*' => 'exists:services,id',

            // Car details
            'photo_of_car' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'model' => 'nullable|string|max:255',
            'production_year' => 'nullable|string|max:4',
            'color' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',

            // Documents
            'driving_license_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'driving_license_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'car_license_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'car_license_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'no_criminal_record' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('drivers.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $driverData = $request->except([
            'photo',
            'photo_of_car',
            'driving_license_front',
            'driving_license_back',
            'car_license_front',
            'car_license_back',
            'no_criminal_record',
            'password',
            'option_ids',
            'primary_service_id',
            'optional_service_ids'
        ]);

        // Handle password
        if ($request->filled('password')) {
            $driverData['password'] = Hash::make($request->password);
        }

        // Handle all image uploads
        $imageFields = [
            'photo',
            'photo_of_car',
            'driving_license_front',
            'driving_license_back',
            'car_license_front',
            'car_license_back'
        ];

        foreach ($imageFields as $field) {
            if ($request->has($field)) {
                // Delete old file if exists
                if ($driver->$field && file_exists('assets/admin/uploads/' . $driver->$field)) {
                    unlink('assets/admin/uploads/' . $driver->$field);
                }

                $driverData[$field] = uploadImage('assets/admin/uploads', $request->$field);
            }
        }

        $driver->update($driverData);

        // Sync options
        if ($request->has('option_ids') && is_array($request->option_ids)) {
            $driver->options()->sync($request->option_ids);
        } else {
            $driver->options()->detach();
        }

        // Handle services
        $servicesToSync = [];

        // Add primary service (service_type = 1, status = 1 - always active)
        if ($request->has('primary_service_id')) {
            $servicesToSync[$request->primary_service_id] = [
                'service_type' => 1,
                'status' => 1
            ];
        }

        // Add optional services (service_type = 2, status = 1 by default)
        if ($request->has('optional_service_ids') && is_array($request->optional_service_ids)) {
            foreach ($request->optional_service_ids as $serviceId) {
                if ($serviceId != $request->primary_service_id) {
                    $servicesToSync[$serviceId] = [
                        'service_type' => 2,
                        'status' => 1
                    ];
                }
            }
        }

        // Sync services
        $driver->services()->sync($servicesToSync);

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver updated successfully');
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);

        // Delete all driver images if they exist
        $imageFields = [
            'photo',
            'photo_of_car',
            'driving_license_front',
            'driving_license_back',
            'car_license_front',
            'car_license_back'
        ];

        foreach ($imageFields as $field) {
            if ($driver->$field && file_exists('assets/admin/uploads/' . $driver->$field)) {
                unlink('assets/admin/uploads/' . $driver->$field);
            }
        }

        $driver->delete();

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Driver deleted successfully');
    }

    public function topUp(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        if ($request->isMethod('post')) {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'note' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            try {
                // Update driver balance
                $driver->balance += $request->amount;
                $driver->save();

                // Create transaction record
                WalletTransaction::create([
                    'driver_id' => $driver->id,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'amount' => $request->amount,
                    'type_of_transaction' => 1, // 1 for add
                    'note' => $request->note ?? 'شحن رصيد من الشركة',
                ]);

                DB::commit();
                return redirect()->route('drivers.index')
                    ->with('success', __('messages.Balance_Updated_Successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', __('messages.Something_Went_Wrong'));
            }
        }
    }

    public function transactions($id)
    {
        $driver = Driver::with('walletTransactions')->where('id', $id)->first();
        return view('admin.drivers.transactions', compact('driver'));
    }

     public function banForm($id)
    {
        $driver = Driver::findOrFail($id);
        $banReasons = DriverBan::BAN_REASONS;
        
        return view('admin.drivers.ban', compact('driver', 'banReasons'));
    }

    /**
     * Ban a driver
     */
    public function ban(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'ban_reason' => 'required|string',
            'ban_description' => 'nullable|string',
            'ban_type' => 'required|in:temporary,permanent',
            'ban_duration' => 'required_if:ban_type,temporary|nullable|integer|min:1',
            'ban_duration_unit' => 'required_if:ban_type,temporary|nullable|in:hours,days,weeks,months',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $banUntil = null;
            $isPermanent = $request->ban_type === 'permanent';

            if (!$isPermanent) {
                $duration = $request->ban_duration;
                $unit = $request->ban_duration_unit;

                $banUntil = match($unit) {
                    'hours' => Carbon::now()->addHours($duration),
                    'days' => Carbon::now()->addDays($duration),
                    'weeks' => Carbon::now()->addWeeks($duration),
                    'months' => Carbon::now()->addMonths($duration),
                    default => Carbon::now()->addDays($duration),
                };
            }

            // Ban the driver
            $driver->banDriver(
                auth()->guard('admin')->user()->id,
                $request->ban_reason,
                $request->ban_description,
                $banUntil,
                $isPermanent
            );

            DB::commit();
            return redirect()->route('drivers.index')
                ->with('success', 'Driver banned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Unban a driver
     */
    public function unban(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'unban_reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $driver->unbanDriver(
                auth()->guard('admin')->user()->id,
                $request->unban_reason
            );

            DB::commit();
            return redirect()->route('drivers.index')
                ->with('success', 'Driver unbanned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Show ban history
     */
    public function banHistory($id)
    {
        $driver = Driver::with('bans.admin', 'bans.unbannedByAdmin')->findOrFail($id);
        
        return view('admin.drivers.ban-history', compact('driver'));
    }
}
