<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBan;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user-table')->only('index', 'show');
        $this->middleware('permission:user-add')->only('create', 'store');
        $this->middleware('permission:user-edit')->only('edit', 'update', 'topUp');
        $this->middleware('permission:user-delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::with('activeBan');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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

        // Order by newest first
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $users = $query->paginate(15)->appends($request->all());

        return view('admin.users.index', compact('users'));
    }

    public function banForm($id)
    {
        $user = User::findOrFail($id);
        $banReasons = UserBan::BAN_REASONS;

        return view('admin.users.ban', compact('user', 'banReasons'));
    }

    /**
     * Ban a user
     */
    public function ban(Request $request, $id)
    {
        $user = User::findOrFail($id);

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

            // Ban the user
            $user->banUser(
                auth()->guard('admin')->user()->id,
                $request->ban_reason,
                $request->ban_description,
                $banUntil,
                $isPermanent
            );

            DB::commit();
            return redirect()->route('users.index')
                ->with('success', 'User banned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Unban a user
     */
    public function unban(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'unban_reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $user->unbanUser(
                auth()->guard('admin')->user()->id,
                $request->unban_reason
            );

            DB::commit();
            return redirect()->route('users.index')
                ->with('success', 'User unbanned successfully');
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
        $user = User::with('bans.admin', 'bans.unbannedByAdmin')->findOrFail($id);

        return view('admin.users.ban-history', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create');
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
            'phone' => 'required|string|unique:users',
            'email' => 'nullable|email|unique:users',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fcm_token' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'activate' => 'nullable|in:1,2',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('users.create')
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->except('photo');

        // Generate a referral code if not provided
        if (!isset($userData['referral_code'])) {
            $userData['referral_code'] = Str::random(8);
        }

        // Handle photo upload
        if ($request->has('photo')) {
            $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
            $userData['photo'] = $the_file_path;
        }

        User::create($userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
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
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:users,phone,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fcm_token' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'activate' => 'nullable|in:1,2',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('users.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->except('photo');

        // Handle photo upload
        if ($request->has('photo')) {
            $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
            $userData['photo'] = $the_file_path;
        }

        $user->update($userData);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);


        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function topUp(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->isMethod('post')) {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'note' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            try {
                // Update user balance
                $user->balance += $request->amount;
                $user->save();

                // Create transaction record
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'admin_id' => auth()->guard('admin')->user()->id,
                    'amount' => $request->amount,
                    'type_of_transaction' => 1, // 1 for add
                    'note' => $request->note ??  'شحن رصيد من الشركة',
                ]);

                DB::commit();
                return redirect()->route('users.index')
                    ->with('success', __('messages.Balance_Updated_Successfully'));
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', __('messages.Something_Went_Wrong'));
            }
        }
    }
}
