<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        abort_if(config('template.storefront_requires_auth', false), 404);

        return view('auth.register', [
            'redirectTo' => $request->query('redirect_to'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(config('template.storefront_requires_auth', false), 404);

        if ($this->resolveRedirectUrl($request)) {
            $request->session()->put('url.intended', $this->resolveRedirectUrl($request));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

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
