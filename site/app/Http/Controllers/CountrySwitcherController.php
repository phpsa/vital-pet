<?php

namespace App\Http\Controllers;

use App\Support\StorefrontCountry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CountrySwitcherController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'integer'],
        ]);

        StorefrontCountry::set((int) $validated['country_id']);

        // If logged in, persist to the user record as their preferred country
        $user = Auth::user();

        if ($user && in_array((int) $validated['country_id'], StorefrontCountry::allowedCountryIds(), true)) {
            $user->update(['country_id' => (int) $validated['country_id']]);
        }

        return back();
    }
}
