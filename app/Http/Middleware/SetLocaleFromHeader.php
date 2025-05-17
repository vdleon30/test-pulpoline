<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocaleFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $localeHeader = $request->header('Accept-Language');

        if ($localeHeader) {
            $locale = explode(',', $localeHeader)[0];
            $locale = explode('-', $locale)[0];
            if (in_array($locale, config('app.supported_locales', ['en', 'es']))) { 
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}