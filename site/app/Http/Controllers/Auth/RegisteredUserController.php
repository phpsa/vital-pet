<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Listeners\ProvisionCustomerOnRegistration;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\StorefrontCountry;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        abort_if(config('template.storefront_requires_auth', false), 404);

        $showCountrySelector = StorefrontCountry::isEnabled();

        return view('auth.register', [
            'redirectTo' => $request->query('redirect_to'),
            'countries'  => $showCountrySelector
                ? StorefrontCountry::allowedCountries()->pluck('name', 'id')
                : collect(),
            'showCountrySelector' => $showCountrySelector,
            'defaultCountryId' => StorefrontCountry::id(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(config('template.storefront_requires_auth', false), 404);

        if ($this->resolveRedirectUrl($request)) {
            $request->session()->put('url.intended', $this->resolveRedirectUrl($request));
        }

        $showCountrySelector = StorefrontCountry::isEnabled();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => $showCountrySelector
                ? ['required', 'integer', Rule::in(StorefrontCountry::allowedCountryIds())]
                : ['nullable'],
        ]);

        $user = User::query()->create([
            'name'       => trim($validated['first_name'].' '.$validated['last_name']),
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'country_id' => $showCountrySelector ? (int) $validated['country_id'] : null,
        ]);

        event(new Registered($user));

        app(ProvisionCustomerOnRegistration::class)->provision(
            user: $user,
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
        );

        Auth::login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    private function resolveRedirectUrl(Request $request): ?string
    {
        $redirectTo = (string) $request->input('redirect_to', $request->query('redirect_to', ''));

        return match ($redirectTo) {
            'checkout' => route('checkout.view'),
            default => null,
        };
    }
}
