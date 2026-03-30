<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.forgot-password', [
            'redirectTo' => $request->query('redirect_to'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $redirectTo = $this->resolveRedirectKey($request);

        if ($redirectTo) {
            $request->session()->put('auth.redirect_to', $redirectTo);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email', 'redirect_to'))
            ->withErrors(['email' => __($status)]);
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