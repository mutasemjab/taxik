<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDriverActivation
{
    /**
     * Route endpoints that banned drivers ARE allowed to access via POST
     * These are matched against the end of the route path
     */
    protected $bannedDriverAllowedEndpoints = [
        'withdrawal/request',  // Allow withdrawal
        'update_profile',      // Allow profile updates
        'logout',              // Allow logout
        'homeDriver',          // Allow home - matches v1/driver/homeDriver
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('driver-api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get language from header (default to 'en')
        $lang = $request->header('lang', 'en');

        // Check for waiting approval (activate = 3) - completely blocked
        if ($user->activate == 3) {
            $message = $lang === 'ar' ? 'في انتظار الموافقة' : 'Waiting for approval';
            
            return response()->json([
                'message' => $message,
                'status' => 3
            ], 403);
        }

        // Check if driver is banned (activate = 2)
        if ($user->activate == 2) {
            // Allow all GET requests (read-only access)
            if ($request->isMethod('get')) {
                return $next($request);
            }
            
            // For POST/PUT/DELETE requests, check if it's in allowed list
            if ($this->isEndpointAllowed($request->path())) {
                return $next($request);
            }
            
            // Block all other non-GET requests
            $message = $lang === 'ar' 
                ? 'تم حظر حسابك. يمكنك فقط عرض المعلومات وسحب رصيدك.' 
                : 'Your account has been banned. You can only view information and withdraw your balance.';
            
            return response()->json([
                'message' => $message,
                'status' => 2,
                'banned' => true,
                'ban_info' => $this->getBanInfo($user, $lang),
            ], 403);
        }

        // Driver is active (activate = 1)
        return $next($request);
    }

    /**
     * Check if the endpoint is allowed for banned drivers
     * Matches against the end of the path for flexibility
     */
    protected function isEndpointAllowed($currentPath)
    {
        foreach ($this->bannedDriverAllowedEndpoints as $endpoint) {
            // Check if the current path ends with this endpoint
            if (str_ends_with($currentPath, $endpoint)) {
                return true;
            }
            
            // Also check with leading slash
            if (str_ends_with($currentPath, '/' . $endpoint)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get ban information for the driver
     */
   protected function getBanInfo($driver, $lang = 'en')
    {
        $activeBan = $driver->activeBan;
        
        if (!$activeBan) {
            return null;
        }

        $banInfo = [
            'is_permanent' => $activeBan->is_permanent,
            'reason' => $activeBan->ban_reason,
            'reason_text' => $activeBan->getReasonText($lang),
            'description' => $activeBan->ban_description,
            'banned_at' => $activeBan->banned_at->toDateTimeString(),
        ];

        if (!$activeBan->is_permanent && $activeBan->ban_until) {
            $banInfo['ban_until'] = $activeBan->ban_until->toDateTimeString();
            $banInfo['remaining_time'] = $activeBan->getRemainingTime($lang);
        }

        return $banInfo;
    }
}