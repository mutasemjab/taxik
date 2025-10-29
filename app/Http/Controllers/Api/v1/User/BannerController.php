<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\Responses;

class BannerController extends Controller
{
    use Responses;

    public function index()
    {
        $data = Banner::get();
        return $this->success_response('Banners retrieved successfully', $data);
    }

}