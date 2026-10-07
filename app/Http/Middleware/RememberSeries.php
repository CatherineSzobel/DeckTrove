<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers the last series the visitor browsed so the navigation can link to it.
 */
class RememberSeries
{
    public function handle(Request $request, Closure $next): Response
    {
        $series = $request->route('series');

        if (is_string($series) && $request->hasSession() && $request->session()->get('series') !== $series) {
            $request->session()->put('series', $series);
        }

        return $next($request);
    }
}
