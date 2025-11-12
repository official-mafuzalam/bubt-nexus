<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last_seen_at in database
            $user->update(['last_seen_at' => now()]);
            
            // Set user as online in cache for 2 minutes
            Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(2));
        }

        return $next($request);
    }
}