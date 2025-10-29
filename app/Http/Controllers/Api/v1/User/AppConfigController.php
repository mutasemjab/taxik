<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\Notification;
use App\Models\Option;
use App\Traits\Responses;

class AppConfigController extends Controller
{
    use Responses;

    public function appConfig()
    {
        $data = AppConfig::get();
        return $this->success_response('app Config retrieved successfully', $data);
    }

}