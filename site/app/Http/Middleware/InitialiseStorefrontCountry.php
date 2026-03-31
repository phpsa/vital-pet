<?php

namespace App\Http\Middleware;

use App\Support\StorefrontCountry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitialiseStorefrontCountry
{
    public function handle(Request $request, Closure $next): Response
    {
        StorefrontCountry::initialise();

        return $next($request);
    }
}
