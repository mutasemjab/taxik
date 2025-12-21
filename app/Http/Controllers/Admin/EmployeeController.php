<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:employee-table')->only('index', 'show');
        $this->middleware('permission:employee-add')->only('create', 'store');
        $this->middleware('permission:employee-edit')->only('edit', 'update');
        $this->middleware('permission:employee-delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $data = Admin::where('is_super', 0);
        if ($request->search != '' ||  $request->search) {
            $data->where(function ($query) use ($request) {
                $query->where('admins.name', 'LIKE', "%$request->search%")
                    ->orWhere('admins.email',  'LIKE', "%$request->search%")
                    ->orWhere('admins.mobile',  'LIKE', "%$request->search%");
            });
        }
        $data = $data->paginate(10);
        return view('admin.employee.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();
        return view('admin.employee.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:admins,email',
            'username' => 'required|unique:admins,username',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id'
        ]);

        DB::beginTransaction();
        try {
            $admin = new Admin([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_super' => 0
            ]);

            $admin->save();
            $admin->assignRole(Role::find($request->role_id));

            DB::commit();
            return redirect()->route('admin.employee.index')
                ->with('success', __('messages.Employee created successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error Occured", ['message' => $e]);
            return redirect()->route('admin.employee.index')
                ->with('error', __('messages.Something went wrong'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (auth()->user()->can('employee-delete')) {
            DB::beginTransaction();
            try {
                Admin::find($id)->delete();
                DB::table('model_has_roles')->where('model_type', 'App\Models\admin')->where('model_id', $id)->delete();
                DB::commit();
                return redirect()->route('admin.employee.index')
                    ->with('success', 'Admin deleted successfully');
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->route('admin.employee.index')
                    ->with('error', 'Something Error');
            }
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (auth()->user()->can('employee-edit')) {
            $admin = Admin::find($id);
            $roles = Role::all();
            $adminRole = $admin->roles->pluck('id')->all();
            return view('admin.employee.edit', compact('admin', 'roles', 'adminRole'));
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
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
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:admins,email,' . $id,
            'username' => 'required|unique:admins,username,' . $id,
            'role_id' => 'required|exists:roles,id'
        ]);

        DB::beginTransaction();
        try {
            $admin = Admin::find($id);

            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->username = $request->username;
            if ($request->password) {
                $admin->password = Hash::make($request->password);
            }
            $admin->save();

            // Update role
            $admin->syncRoles(Role::find($request->role_id));

            DB::commit();
            return redirect()->route('admin.employee.index')
                ->with('success', __('messages.Employee updated successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error Occured", ['message' => $e]);
            return redirect()->route('admin.employee.index')
                ->with('error', __('messages.Something went wrong'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            Admin::find($id)->delete();
            DB::table('model_has_roles')->where('model_type', 'App\Models\admin')->where('model_id', $id)->delete();
            DB::commit();
            return redirect()->route('admins.index')
                ->with('success', 'Admin deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->route('admins.index')
                ->with('error', 'Something Error');
        }
    }
}
