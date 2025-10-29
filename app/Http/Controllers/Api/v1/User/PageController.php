<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\Responses;

class PageController extends Controller
{
    use Responses;
    
    public function index($type)
    {
        $data = Page::where('type',$type)->first();

        return $this->success_response('Page retrieved successfully', $data);
    }


}
