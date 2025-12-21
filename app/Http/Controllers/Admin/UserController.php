<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
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
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
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

