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
        if (auth()->user()->can('employee-add')) {
            $roles = Role::get();
            return view('admin.employee.create', compact('roles'));
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (auth()->user()->can('employee-add')) {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|unique:admins,email',
                'password' => 'required',
                'roles' => 'required'
            ]);

            DB::beginTransaction();
            try {


                $admin = new Admin([
                    'name' => $request->name,
                    'email' => $request->email,
                    'username' => $request->username,
                    'password' => Hash::make($request->password),

                ]);

                $admin->save();
                foreach ($request->roles as $role) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role,
                        'model_type' => 'App\Models\admin',
                        'model_id' => $admin->id
                    ]);
                }
                DB::commit();
                return redirect()->route('admin.employee.index')
                    ->with('success', 'Employee created successfully');
            } catch (Exception $e) {
                DB::rollBack();
                Log::info("Error Occured", ['message' => $e]);
                return redirect()->route('admin.employee.index')
                    ->with('error', 'Something Wrong');
            }
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
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
        if (auth()->user()->can('employee-edit')) {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|unique:admins,email,' . $id,
                'roles' => 'required'
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
                DB::table('model_has_roles')->where('model_type', 'App\Models\admin')
                    ->where('model_id', $id)->delete();
                foreach ($request->roles as $role) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role,
                        'model_type' => 'App\Models\admin',
                        'model_id' => $admin->id
                    ]);
                }
                DB::commit();
                return redirect()->route('admin.employee.index')
                    ->with('success', 'Employee updated successfully');
            } catch (Exception $e) {
                DB::rollBack();
                Log::info("Error Occured", ['message' => $e]);
                return redirect()->route('admin.employee.index')
                    ->with('error', 'Something Wrong');
            }
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
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
