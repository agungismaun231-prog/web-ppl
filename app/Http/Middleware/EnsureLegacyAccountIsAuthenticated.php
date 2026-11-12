<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegacyAccountIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('legacy_account')) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}