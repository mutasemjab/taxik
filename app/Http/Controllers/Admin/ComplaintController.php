<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:complaint-table')->only('index', 'show');
        $this->middleware('permission:complaint-add')->only('create', 'store');
        $this->middleware('permission:complaint-edit')->only('edit', 'update', 'updateStatus');
        $this->middleware('permission:complaint-delete')->only('destroy');
    }

    /**
     * Display a listing of the complaints.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $complaints = Complaint::with(['user', 'driver', 'order'])->latest()->paginate(10);
        return view('admin.complaints.index', compact('complaints'));
    }

 
    /**
     * Display the specified complaint.
     *
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function show(Complaint $complaint)
    {
        $complaint->load(['user', 'driver', 'order']);
        return view('admin.complaints.show', compact('complaint'));
    }

   

    /**
     * Update complaint status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $complaint->update(['status' => $request->status]);

        return back()->with('success', __('messages.Status_Updated_Successfully'));
    }

    /**
     * Remove the specified complaint from storage.
     *
     * @param  \App\Models\Complaint  $complaint
     * @return \Illuminate\Http\Response
     */
    public function destroy(Complaint $complaint)
    {
        $complaint->delete();

        return redirect()->route('admin.complaints.index')
            ->with('success', __('messages.Complaint_Deleted_Successfully'));
    }
}