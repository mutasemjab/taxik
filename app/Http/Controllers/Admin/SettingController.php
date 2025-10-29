<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{

  public function index()
  {

    $data = Setting::paginate(PAGINATION_COUNT);

    return view('admin.settings.index', ['data' => $data]);
  }



  public function edit($id)
  {
       if (auth()->user()->can('setting-edit')) {
    $data=Setting::findorFail($id);
    return view('admin.settings.edit',compact('data'));
       }else{
            return redirect()->back()
            ->with('error', "Access Denied");
       }
  }

  public function update(Request $request, $id)
  {
      if (auth()->user()->can('setting-edit')) {
          $setting = Setting::findOrFail($id);

          // Validate input (only allow 'value' field)
          $request->validate([
              'value' => 'required|integer', // Adjust validation as needed
          ]);

          try {
              // Update only the value field
              $setting->value = $request->input('value');

              if ($setting->save()) {
                  return redirect()->route('admin.setting.index')->with(['success' => 'Setting updated successfully']);
              } else {
                  return redirect()->back()->with(['error' => 'Something went wrong']);
              }
          } catch (\Exception $ex) {
              return redirect()->back()
                  ->with(['error' => 'An error occurred: ' . $ex->getMessage()])
                  ->withInput();
          }
      } else {
          return redirect()->back()->with('error', "Access Denied");
      }
  }







}
