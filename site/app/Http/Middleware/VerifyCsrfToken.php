<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Route;

class VerifyCsrfToken extends Middleware
{
    protected function inExceptArray($request): bool
    {
        if (parent::inExceptArray($request)) {
            return true;
        }

        $landingPath = $this->landingPath();

        return $landingPath !== null && $request->is($landingPath);
    }

    protected function landingPath(): ?string
    {
        if (! Route::has('landing.special')) {
            return null;
        }

        $relativeUrl = route('landing.special', [], false);
        $path = trim((string) parse_url($relativeUrl, PHP_URL_PATH), '/');

        return $path !== '' ? $path : null;
    }
}
