<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Setting;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    use Responses;

    public function store(Request $request)
    {
        // Add validation to ensure proper data is submitted
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'nullable|string|max:500',
            'driver_id' => 'required|exists:drivers,id',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $rating = new Rating();
        
        // Associate the rating with the authenticated user
        $rating->user_id = auth()->user()->id;
        $rating->rating = $request->rating;
        $rating->review = $request->review;
        $rating->driver_id = $request->driver_id;
        $rating->order_id = $request->order_id;
    
        if ($rating->save()) {
            return $this->success_response('Rating submitted successfully', $rating);
        } else {
            return $this->error_response('Something went wrong', null);
        }
    }
}
