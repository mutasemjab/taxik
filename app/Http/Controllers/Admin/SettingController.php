<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:setting-table')->only('index', 'show');
        $this->middleware('permission:setting-edit')->only('edit', 'update');
    }

    public function index()
    {
        $data = Setting::paginate(20);
        return view('admin.settings.index', ['data' => $data]);
    }

    public function edit($id)
    {
        $data = Setting::findorFail($id);
        return view('admin.settings.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        // Validate input (only allow 'value' field)
        $request->validate([
            'value' => 'required', // Adjust validation as needed
        ]);

        try {
            // Update only the value field
            $setting->value = $request->input('value');

            if ($setting->save()) {
                return redirect()->route('settings.index')->with(['success' => 'Setting updated successfully']);
            } else {
                return redirect()->back()->with(['error' => 'Something went wrong']);
            }
        } catch (\Exception $ex) {
            return redirect()->back()
                ->with(['error' => 'An error occurred: ' . $ex->getMessage()])
                ->withInput();
        }
    }
}
