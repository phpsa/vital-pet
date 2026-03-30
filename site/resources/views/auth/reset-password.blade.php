<x-layouts.auth title="Reset Password">
    <div class="ves-auth-copy">
        <span class="ves-kicker">Set a new password</span>
        <h1 class="ves-serif ves-auth-title">Reset password</h1>
        <p class="ves-auth-text">
            Choose a new password for your account and sign back in.
        </p>
    </div>

    <form class="ves-auth-form"
          method="POST"
          action="{{ route('password.update') }}">
        @csrf

        <input name="token"
               type="hidden"
               value="{{ $request->route('token') }}">

         @if (! empty($redirectTo))
             <input name="redirect_to"
                 type="hidden"
                 value="{{ $redirectTo }}">
         @endif

        <label class="ves-auth-field">
            <span>Email address</span>
            <input name="email"
                   type="email"
                   value="{{ old('email', $request->email) }}"
                   required
                   autofocus
                   autocomplete="email">
        </label>
        @error('email')
            <p class="ves-auth-error">{{ $message }}</p>
        @enderror

        <label class="ves-auth-field">
            <span>New password</span>
            <input name="password"
                   type="password"
                   required
                   autocomplete="new-password">
        </label>
        @error('password')
            <p class="ves-auth-error">{{ $message }}</p>
        @enderror

        <label class="ves-auth-field">
            <span>Confirm password</span>
            <input name="password_confirmation"
                   type="password"
                   required
                   autocomplete="new-password">
        </label>

        <button class="ves-button ves-button-primary ves-auth-submit"
                type="submit">
            Reset password
        </button>
    </form>
</x-layouts.auth>