<?php

namespace Loctracker\ActivityLogger\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IgnoreLoggingRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ignorePatterns = config('activity-logger.ignore_routes', []);
        $currentPath = $request->path();

        foreach ($ignorePatterns as $pattern) {
            if (Str::is($pattern, $currentPath)) {
                // Set a flag in the request to indicate this route should be ignored
                $request->attributes->set('activity-logger.ignore', true);
                break;
            }
        }

        return $next($request);
    }
}
