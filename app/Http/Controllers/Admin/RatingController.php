<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\Ahadeeth;
use App\Models\AhadeethClass;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;




class RatingController extends Controller
{
    /**
     * Display a listing of the ratings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Rating::with(['user', 'driver']);

        // Filter by rating
        if ($request->has('rating') && $request->rating != '') {
            $query->where('rating', $request->rating);
        }

        // Filter by driver
        if ($request->has('driver_id') && $request->driver_id != '') {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('review', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $ratings = $query->latest()->paginate(10);
        
        // Get statistics
        $statistics = [
            'total' => Rating::count(),
            'average' => round(Rating::avg('rating'), 1),
            'five_star' => Rating::where('rating', 5)->count(),
            'four_star' => Rating::where('rating', 4)->count(),
            'three_star' => Rating::where('rating', 3)->count(),
            'two_star' => Rating::where('rating', 2)->count(),
            'one_star' => Rating::where('rating', 1)->count(),
        ];

        return view('admin.ratings.index', compact('ratings', 'statistics'));
    }

   

    /**
     * Remove the specified rating from storage.
     *
     * @param  \App\Models\Rating  $rating
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rating $rating)
    {
        $rating->delete();

        return redirect()->route('ratings.index')
            ->with('success', __('messages.Rating_Deleted_Successfully'));
    }
}
