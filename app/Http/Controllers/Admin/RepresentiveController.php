<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Representive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepresentiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:representive-table', ['only' => ['index']]);
        $this->middleware('permission:representive-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:representive-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:representive-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $representives = Representive::latest()->paginate(10);
        return view('admin.representives.index', compact('representives'));
    }

    public function create()
    {
        return view('admin.representives.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:representives,phone',
            'commission' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Representive::create($request->all());

        return redirect()->route('representives.index')
            ->with('success', __('messages.representative_added_successfully'));
    }

    public function edit(Representive $representive)
    {
        return view('representives.edit', compact('representive'));
    }

    public function update(Request $request, Representive $representive)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:representives,phone,' . $representive->id,
            'commission' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $representive->update($request->all());

        return redirect()->route('representives.index')
            ->with('success', __('messages.representative_updated_successfully'));
    }

    public function destroy(Representive $representive)
    {
        $representive->delete();

        return redirect()->route('representives.index')
            ->with('success', __('messages.representative_deleted_successfully'));
    }
}