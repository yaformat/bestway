<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Log the request data
        Log::info('API Request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'params' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Continue processing the request
        return $next($request);
    }
}