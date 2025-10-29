<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDriverActivation
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('driver-api')->user(); // if you're using 'driver' guard

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check activate status
        if ($user->activate == 2) {
            return response()->json(['message' => 'You are not active','status'=>2], 403);
        }

        // Check for waiting approval
        if ($user->activate == 3) {
            return response()->json(['message' => 'Waiting for approval','status'=>3], 403);
        }

        return $next($request);
    }
}
