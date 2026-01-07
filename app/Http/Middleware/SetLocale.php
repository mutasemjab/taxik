<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
     public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('lang', 'en');
        
        // Only allow supported languages
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'en';
        }
        
        app()->setLocale($locale);
        
        return $next($request);
    }
}

