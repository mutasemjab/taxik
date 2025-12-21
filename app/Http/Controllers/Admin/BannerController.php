<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:banner-table')->only('index', 'show');
        $this->middleware('permission:banner-add')->only('create', 'store');
        $this->middleware('permission:banner-edit')->only('edit', 'update');
        $this->middleware('permission:banner-delete')->only('destroy');
    }

    public function index()
    {
        $banners = DB::table('banners')->orderBy('created_at', 'desc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $photoPath = uploadImage('assets/admin/uploads', $request->photo);

        DB::table('banners')->insert([
            'photo' => $photoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('banners.index')->with('success', __('messages.banner_created'));
    }

    public function edit($id)
    {
        $banner = DB::table('banners')->where('id', $id)->first();
        if (!$banner) {
            return redirect()->route('banners.index')->with('error', __('messages.banner_not_found'));
        }
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $banner = DB::table('banners')->where('id', $id)->first();
        if (!$banner) {
            return redirect()->route('banners.index')->with('error', __('messages.banner_not_found'));
        }

        $updateData = [
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo')) {
            $updateData['photo']= uploadImage('assets/admin/uploads', $request->photo);
        }

        DB::table('banners')->where('id', $id)->update($updateData);

        return redirect()->route('banners.index')->with('success', __('messages.banner_updated'));
    }

    public function destroy($id)
    {
        $banner = DB::table('banners')->where('id', $id)->first();
        if ($banner) {
            DB::table('banners')->where('id', $id)->delete();
        }
        return redirect()->route('banners.index')->with('success', __('messages.banner_deleted'));
    }
}