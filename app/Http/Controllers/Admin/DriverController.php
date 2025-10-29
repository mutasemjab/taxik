<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Option;
use App\Models\WalletTransaction;
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
            'photo', 'photo_of_car', 'driving_license_front', 
            'driving_license_back', 'car_license_front', 'car_license_back','no_criminal_record',
            'password'
        ]);

     
        
        // Set default values
        $driverData['balance'] = $request->has('balance') ? $request->balance : 0;
        $driverData['activate'] = $request->has('activate') ? $request->activate : 1;

        // Handle all image uploads
        $imageFields = [
            'photo', 'photo_of_car', 'driving_license_front', 
            'driving_license_back', 'car_license_front', 'car_license_back'
        ];
        
        foreach ($imageFields as $field) {
            if ($request->has($field)) {
                $driverData[$field] = uploadImage('assets/admin/uploads', $request->$field);
            }
        }

        $driver =  Driver::create($driverData);

        // Attach options to the driver
            if ($request->has('option_ids') && is_array($request->option_ids)) {
                $driver->options()->attach($request->option_ids);
            }
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
        $driver = Driver::findOrFail($id);
        $options = Option::all();
        
        return view('admin.drivers.edit', compact('driver', 'options'));
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
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|unique:drivers,phone,' . $id,
        'email' => 'nullable|email|unique:drivers,email,' . $id,
        'sos_phone' => 'nullable|string',
        'option_ids' => 'required|array',
        'option_ids.*' => 'required|exists:options,id',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        
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
            'photo', 'photo_of_car', 'driving_license_front', 
            'driving_license_back', 'car_license_front', 'car_license_back','no_criminal_record',
            'password', 'option_ids'
        ]);

        // Handle password
        if ($request->filled('password')) {
            $driverData['password'] = Hash::make($request->password);
        }

        // Handle all image uploads
        $imageFields = [
            'photo', 'photo_of_car', 'driving_license_front', 
            'driving_license_back', 'car_license_front', 'car_license_back'
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
        // Sync options (removes old associations and adds new ones)
            if ($request->has('option_ids') && is_array($request->option_ids)) {
                $driver->options()->sync($request->option_ids);
            } else {
                // Clear all options if none are selected
                $driver->options()->detach();
            }
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
            'photo', 'photo_of_car', 'driving_license_front', 
            'driving_license_back', 'car_license_front', 'car_license_back'
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
                    'note' => $request->note ?? 'Balance top-up by admin',
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
        $driver = Driver::with('walletTransactions')->where('id',$id)->first();
        return view('admin.drivers.transactions',compact('driver'));
    }

}
