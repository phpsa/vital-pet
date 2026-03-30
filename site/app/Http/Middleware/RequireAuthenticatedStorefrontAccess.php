<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthenticatedStorefrontAccess
{
    /**
     * Require an authenticated user for storefront routes when the
     * storefront auth lock switch is enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('template.storefront_requires_auth', false)) {
            return $next($request);
        }

        if (Auth::check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(401);
        }

        return redirect()->guest(route('login'));
    }
}
