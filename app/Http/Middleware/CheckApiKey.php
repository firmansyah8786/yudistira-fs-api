<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
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
        if(!isset($request->api_key))
            return response()->json([
                'error'=>'Insert API key'
            ],401);

        if($request->api_key != env('API_KEY'))
            return response()->json([
                'error'=>'Wrong API key'
            ],401);

        return $next($request);
    }
}
