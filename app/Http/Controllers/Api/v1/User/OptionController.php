<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Option;
use App\Traits\Responses;

class OptionController extends Controller
{
    use Responses;

    public function getOptions()
    {
        $data = Option::get();
        return $this->success_response('Options retrieved successfully', $data);
    }

}
