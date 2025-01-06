<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RequestTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the request's Content-Type is application/json
        if (! $request->isJson()) {
            return response()->json(['error' => 'Invalid Content-Type. Only application/json is accepted.'], 415);
        }

        return $next($request);
    }
}
