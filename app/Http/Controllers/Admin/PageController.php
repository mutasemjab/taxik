<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::paginate(PAGINATION_COUNT);
        return view('admin.pages.index', ['pages' => $pages]);
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $page = new Page();
        $page->type = $request->input('type');
        $page->title = $request->input('title');
        $page->content = $request->input('content');
        $page->save();

        return redirect()->route('pages.index',);
    }

    public function edit($id)
    {
        $data = Page::findOrFail($id);
        return view('admin.pages.edit', ['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $page->title = $request->input('title');
        $page->content = $request->input('content');
        $page->save();

        return redirect()->route('pages.index',);
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $type = $page->type;
        $page->delete();

        return redirect()->route('pages.index');
    }
}
