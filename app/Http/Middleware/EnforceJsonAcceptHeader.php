<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceJsonAcceptHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        if (!$request->hasHeader('Accept') ||
            (strpos(strtolower($request->header('Accept')), 'application/json') === false &&
             strpos($request->header('Accept'), '*/*') === false)) {
            return response()->json(['message' => __('The Accept header must be set to application/json.')], 406);
        }

        return $next($request);
    }
}
