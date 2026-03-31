<?php

namespace App\Http\Controllers;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\StorefrontCountry;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class InvitationController extends Controller
{
    /**
     * Display the profile invite form for authenticated users.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $sentInvitations = $user->invitations()
            ->orderByDesc('created_at')
            ->get();

        return view('account.invite', [
            'activeTab'        => 'invite',
            'sentInvitations'  => $sentInvitations,
        ]);
    }

    /**
     * Send an invitation from the authenticated user's profile.
     */
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $request->input('email');

        // Block if the email already belongs to a registered user.
        if (User::where('email', $email)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'That email address already has an account.']);
        }

        // Block if there is already a pending invitation for this email.
        $existing = Invitation::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'A pending invitation has already been sent to that address.']);
        }

        $invitation = Invitation::generate(
            email: $email,
            invitedByUserId: $request->user()->id,
            isStaffInvite: false,
        );

        Mail::to($email)->send(new InvitationMail($invitation));

        return back()->with('status', "Invitation sent to {$email}.");
    }

    /**
     * Show the invited registration form.
     */
    public function showRegistration(string $token): View
    {
        $invitation = $this->findValidInvitation($token);

        $showCountrySelector = StorefrontCountry::isEnabled();

        return view('auth.register-invited', [
            'invitation'          => $invitation,
            'showCountrySelector' => $showCountrySelector,
            'countries'           => $showCountrySelector
                ? StorefrontCountry::allowedCountries()->pluck('name', 'id')
                : collect(),
            'defaultCountryId'    => StorefrontCountry::id(),
        ]);
    }

    /**
     * Handle invited user registration.
     */
    public function register(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->findValidInvitation($token);

        $showCountrySelector = StorefrontCountry::isEnabled();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => $showCountrySelector
                ? ['required', 'integer', Rule::in(StorefrontCountry::allowedCountryIds())]
                : ['nullable'],
        ]);

        // The referred_by_id is the inviting user's id, or null for staff invites.
        $referredById = $invitation->is_staff_invite
            ? null
            : $invitation->invited_by_user_id;

        $user = User::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'password'       => Hash::make($validated['password']),
            'country_id'     => $showCountrySelector ? (int) $validated['country_id'] : null,
            'referred_by_id' => $referredById,
        ]);

        $invitation->markUsed();

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    private function findValidInvitation(string $token): Invitation
    {
        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || $invitation->isUsed() || $invitation->isExpired()) {
            abort(404, 'This invitation link is invalid or has expired.');
        }

        return $invitation;
    }
}
