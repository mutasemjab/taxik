<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Gate;
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    
        if(!Gate::allows('role-table'))
            return "Not auth";
        // $manager = Manager::where('email', auth()->user()->email)->first();
        // $shop = $manager->shop;

        if ($request->search != '' ||  $request->search) {
            $data = Role::where(function ($query) use ($request) {
                $query->where('roles.name', 'LIKE', "%$request->search%")
                    ->orWhere('roles.guard_name',  'LIKE', "%$request->search%");
            })->paginate(10);
        } else {
            $data = Role::paginate(10);
        }
         return view('admin.roles.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $data = Permission::where('guard_name','admin')->get();
        return view('admin.roles.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate(
            [
                'name' => 'required|unique:roles,name',
                 'perms' => 'required',
            ]
        );
        DB::beginTransaction();
        try {

            $role = new Role([
                "name" => $request->name,
                "guard_name" => 'admin',

            ]);
            $role->save();
            $data = [];
            foreach ($request->perms as $permission) {
                $data[] = [
                    'role_id' => $role->id,
                    'permission_id' => $permission
                ];
            };

            DB::table('role_has_permissions')->insertOrIgnore($data);

            DB::commit();
            return redirect()->route('admin.role.index')->with('success', trans('messages.success'));
        } catch (Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $permissions = Permission::where('guard_name','admin')->get();
        $role_permissions = DB::table('role_has_permissions')->where('role_id',$id)->pluck('permission_id')->toArray();
        $data = Role::find($id);
         return view('admin.roles.edit', compact('permissions','role_permissions','data'));
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


        DB::beginTransaction();
        try {
            $role = Role::find($id);
            $role->name = $request->name;
            $role->guard_name = 'admin';


            $role->save();
            $role_permissions = DB::table('role_has_permissions')->where('role_id',$id)->delete();
            $data = [];
            foreach ($request->perms as $permission) {
                $data[] = [
                    'role_id' => $role->id,
                    'permission_id' => $permission
                ];
            };

            DB::table('role_has_permissions')->insertOrIgnore($data);

            DB::commit();
            return redirect()->route('admin.role.index')->with('success', trans('messages.success'));
        } catch (Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {

        Role::where('id',$request->id)->delete();
       return 1;

    }
}
