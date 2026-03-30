<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        $redirectTo = $this->resolveRedirectKey($request) ?: $request->session()->get('auth.redirect_to');

        return view('auth.reset-password', [
            'request' => $request,
            'redirectTo' => $redirectTo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $redirectTo = $this->resolveRedirectKey($request) ?: $request->session()->get('auth.redirect_to');

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $request->session()->forget('auth.redirect_to');

            if ($redirectTo === 'checkout') {
                return redirect()
                    ->route('login', ['redirect_to' => 'checkout'])
                    ->with('status', __($status));
            }

            return redirect()->route('login')->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email', 'redirect_to'))
            ->withErrors(['email' => [__($status)]]);
    }

    private function resolveRedirectKey(Request $request): ?string
    {
        $redirectTo = (string) $request->input('redirect_to', $request->query('redirect_to', ''));

        return match ($redirectTo) {
            'checkout' => 'checkout',
            default => null,
        };
    }
}