<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $redirectTo = request()->query('redirect_to');

        return view('auth.login', [
            'redirectTo' => $redirectTo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->resolveRedirectUrl($request)) {
            $request->session()->put('url.intended', $this->resolveRedirectUrl($request));
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
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