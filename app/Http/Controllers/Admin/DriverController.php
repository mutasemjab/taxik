<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverBan;
use App\Models\DriverRegistrationPayment;
use App\Models\Option;
use App\Models\Representive;
use App\Models\Service;
use App\Models\Setting;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:driver-table')->only('index', 'show', 'banHistory');
        $this->middleware('permission:driver-add')->only('create', 'store');
        $this->middleware('permission:driver-edit')->only('edit', 'update', 'topUp', 'transactions', 'banForm', 'ban', 'unban');
        $this->middleware('permission:driver-delete')->only('destroy');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Driver::with(['options', 'activeBan']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('activate', $request->status);
        }

        // Filter by balance
        if ($request->has('min_balance') && $request->min_balance != '') {
            $query->where('balance', '>=', $request->min_balance);
        }

        if ($request->has('max_balance') && $request->max_balance != '') {
            $query->where('balance', '<=', $request->max_balance);
        }

        // Filter by last login date range
        if ($request->has('last_login_from') && $request->last_login_from != '') {
            $query->where('last_login', '>=', $request->last_login_from);
        }

        if ($request->has('last_login_to') && $request->last_login_to != '') {
            $query->where('last_login', '<=', $request->last_login_to . ' 23:59:59');
        }

        // Filter by online status (logged in within last 5 minutes)
        if ($request->has('online_status') && $request->online_status != '') {
            if ($request->online_status == 'online') {
                $query->where('last_login', '>=', now()->subMinutes(5));
            } elseif ($request->online_status == 'offline') {
                $query->where(function ($q) {
                    $q->where('last_login', '<', now()->subMinutes(5))
                        ->orWhereNull('last_login');
                });
            }
        }

        // Order by newest first (or by last_login if specified)
        if ($request->has('sort_by') && $request->sort_by == 'last_login') {
            $query->orderBy('last_login', 'desc')->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        $drivers = $query->paginate(15)->appends($request->all());

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
        $services = Service::all(); // Add this line to fetch all services

        // جلب القيمة الافتراضية من الإعدادات
        $defaultBalance = Setting::where('key', 'new_driver_register_add_balance')->first()->value ?? 0;

        return view('admin.drivers.create', compact('options', 'services', 'defaultBalance'));
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
            'option_ids' => 'nullable|array', // Changed from 'required' to 'nullable'
            'option_ids.*' => 'exists:options,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'total_paid' => 'nullable|numeric|min:0',
            'amount_kept' => 'nullable|numeric|min:0',
            'amount_added_to_wallet' => 'nullable|numeric|min:0',
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
            'optional_service_ids',
            'total_paid',
            'amount_kept',
            'amount_added_to_wallet',
            'payment_note'
        ]);

        // الرصيد الابتدائي يكون فقط من amount_added_to_wallet
        $driverData['balance'] = $request->amount_added_to_wallet ?? 0;
        $driverData['activate'] = $request->has('activate') ? $request->activate : 1;

        // معالجة الصور
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

        // ربط الخيارات
        if ($request->has('option_ids') && is_array($request->option_ids)) {
            $driver->options()->attach($request->option_ids);
        }

        // ربط الخدمات
        $servicesToAttach = [];

        if ($request->has('primary_service_id')) {
            $servicesToAttach[$request->primary_service_id] = [
                'service_type' => 1,
                'status' => 1
            ];
        }

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

        $driver->services()->attach($servicesToAttach);

        // إضافة دفعة التسجيل (فقط إذا كان هناك قيم)
        if ($request->total_paid > 0 || $request->amount_kept > 0 || $request->amount_added_to_wallet > 0) {
            DriverRegistrationPayment::create([
                'driver_id' => $driver->id,
                'total_paid' => $request->total_paid ?? 0,
                'amount_kept' => $request->amount_kept ?? 0,
                'amount_added_to_wallet' => $request->amount_added_to_wallet ?? 0,
                'note' => $request->payment_note,
                'admin_id' => Auth::guard('admin')->id(),
            ]);

            // إضافة معاملة محفظة فقط إذا تم إضافة رصيد
            if ($request->amount_added_to_wallet > 0) {
                WalletTransaction::create([
                    'driver_id' => $driver->id,
                    'admin_id' => Auth::guard('admin')->id(),
                    'amount' => $request->amount_added_to_wallet,
                    'type_of_transaction' => 1, // إضافة
                    'note' => 'رصيد تسجيل أولي - ' . ($request->payment_note ?? ''),
                ]);
            }
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
        $driver = Driver::with('registrationPayments.admin')->findOrFail($id);
        $options = Option::all();
        $allServices = Service::where('activate', 1)->get();
        $representatives = Representive::get();

        // Get services already assigned to driver
        $driverServiceIds = $driver->services->pluck('id')->toArray();

        // جلب القيمة الافتراضية من الإعدادات
        $defaultBalance = Setting::where('key', 'new_driver_register_add_balance')->first()->value ?? 0;

        return view('admin.drivers.edit', compact('driver', 'options', 'allServices', 'driverServiceIds', 'representatives', 'defaultBalance'));
    }

    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:drivers,phone,' . $id,
            'email' => 'nullable|email|unique:drivers,email,' . $id,
            'sos_phone' => 'nullable|string',
            'representive_id' => 'nullable|exists:representives,id',
            'option_ids' => 'required|array',
            'option_ids.*' => 'required|exists:options,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'total_paid' => 'nullable|numeric|min:0',
            'amount_kept' => 'nullable|numeric|min:0',
            'amount_added_to_wallet' => 'nullable|numeric|min:0',
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

        // استبعاد حقول الدفع من بيانات السائق
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
            'optional_service_ids',
            'total_paid',           // إزالة
            'amount_kept',          // إزالة
            'amount_added_to_wallet', // إزالة
            'payment_note'          // إزالة
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
            'car_license_back',
            'no_criminal_record',
        ];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
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

        // إذا تم إضافة دفعة جديدة (فقط إذا كان هناك قيم)
        if ($request->total_paid > 0 || $request->amount_kept > 0 || $request->amount_added_to_wallet > 0) {
            DriverRegistrationPayment::create([
                'driver_id' => $driver->id,
                'total_paid' => $request->total_paid ?? 0,
                'amount_kept' => $request->amount_kept ?? 0,
                'amount_added_to_wallet' => $request->amount_added_to_wallet ?? 0,
                'note' => $request->payment_note,
                'admin_id' => Auth::guard('admin')->id(),
            ]);

            // تحديث رصيد السائق وإضافة معاملة محفظة
            if ($request->amount_added_to_wallet > 0) {
                $driver->increment('balance', $request->amount_added_to_wallet);

                WalletTransaction::create([
                    'driver_id' => $driver->id,
                    'admin_id' => Auth::guard('admin')->id(),
                    'amount' => $request->amount_added_to_wallet,
                    'type_of_transaction' => 1, // إضافة
                    'note' => 'دفعة إضافية - ' . ($request->payment_note ?? ''),
                ]);
            }
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

                $banUntil = match ($unit) {
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
