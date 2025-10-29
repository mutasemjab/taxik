<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;

use App\Models\POS;
use Illuminate\Http\Request;

class POSController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posRecords = POS::latest()->paginate(10);
        return view('admin.pos.index', compact('posRecords'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        POS::create($request->all());

        return redirect()->route('pos.index')
            ->with('success', __('messages.pos_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(POS $po)
    {
        return view('pos.show', compact('po'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(POS $po)
    {
        return view('admin.pos.edit', compact('po'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, POS $po)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $po->update($request->all());

        return redirect()->route('pos.index')
            ->with('success', __('messages.pos_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(POS $po)
    {
        $po->delete();

        return redirect()->route('pos.index')
            ->with('success', __('messages.pos_deleted_successfully'));
    }
}