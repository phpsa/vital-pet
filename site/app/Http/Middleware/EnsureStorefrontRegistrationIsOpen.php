<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontRegistrationIsOpen
{
    /**
     * Allow storefront self-registration only when the access lock is disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('template.storefront_requires_auth', false)) {
            abort(404);
        }

        return $next($request);
    }
}
