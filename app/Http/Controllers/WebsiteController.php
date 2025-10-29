<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
{

    public function index()
    {
        return view('user.home');
    }
 
    public function privacyPolicy()
    {
        $page = Page::where('type',3)->first();

        return view('user.privacy-policy',compact('page'));
    }
}