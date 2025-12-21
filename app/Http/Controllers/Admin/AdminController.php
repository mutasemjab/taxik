<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin-table')->only('index', 'show');
        $this->middleware('permission:admin-add')->only('create', 'store');
        $this->middleware('permission:admin-edit')->only('edit', 'update');
        $this->middleware('permission:admin-delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $data = Admin::where('is_super', 1);
        if ($request->search != '' || $request->search) {
            $data->where(function ($query) use ($request) {
                $query->where('admins.name', 'LIKE', "%$request->search%")
                    ->orWhere('admins.email', 'LIKE', "%$request->search%")
                    ->orWhere('admins.username', 'LIKE', "%$request->search%");
            });
        }
        $data = $data->paginate(10);
        return view('admin.admin.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.admin.create');
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
            'is_super' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $admin = new Admin([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_super' => $request->is_super
            ]);

            $admin->save();
            DB::commit();
            return redirect()->route('admin.admin.index')
                ->with('success', __('messages.Admin_created_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error Occurred", ['message' => $e]);
            return redirect()->route('admin.admin.index')
                ->with('error', __('messages.Something_went_wrong'));
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
        $admin = Admin::find($id);
        if (!$admin || $admin->is_super != 1) {
            return redirect()->back()
                ->with('error', __('messages.Admin_not_found'));
        }
        return view('admin.admin.edit', compact('admin'));
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
            'is_super' => 'required|boolean'
        ]);

        DB::beginTransaction();
        try {
            $admin = Admin::find($id);

            if (!$admin || $admin->is_super != 1) {
                return redirect()->back()
                    ->with('error', __('messages.Admin_not_found'));
            }

            // Prevent demoting the super admin (id = 1)
            if ($admin->id == 1 && !$request->is_super) {
                return redirect()->back()
                    ->with('error', __('messages.Cannot_demote_super_admin'));
            }

            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->username = $request->username;
            $admin->is_super = $request->is_super;
            if ($request->password) {
                $admin->password = Hash::make($request->password);
            }
            $admin->save();
            DB::commit();
            return redirect()->route('admin.admin.index')
                ->with('success', __('messages.Admin_updated_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::info("Error Occurred", ['message' => $e]);
            return redirect()->route('admin.admin.index')
                ->with('error', __('messages.Something_went_wrong'));
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
            $admin = Admin::find($id);

            if (!$admin || $admin->is_super != 1) {
                return redirect()->back()
                    ->with('error', __('messages.Admin_not_found'));
            }

            // Prevent deleting the super admin (id = 1)
            if ($admin->id == 1) {
                return redirect()->back()
                    ->with('error', __('messages.Cannot_delete_super_admin'));
            }

            $admin->delete();
            DB::commit();
            return redirect()->route('admin.admin.index')
                ->with('success', __('messages.Admin_deleted_successfully'));
        } catch (Exception $e) {
            DB::rollback();
            Log::info("Error Occurred", ['message' => $e]);
            return redirect()->back()
                ->with('error', __('messages.Something_went_wrong'));
        }
    }
}