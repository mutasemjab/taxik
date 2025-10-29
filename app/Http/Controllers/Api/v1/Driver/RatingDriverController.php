<?php


namespace App\Http\Controllers\Api\v1\Driver;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Setting;

class RatingDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
    {
        $driver_id = auth()->guard('driver-api')->user()->id;
        $data = Rating::with('user','order')->where('driver_id',$driver_id)->get();

       return $this->success_response('Rating get successfully', $data);
    }


}
