<x-layouts.auth title="Create Account">
    <div class="ves-auth-copy">
        <span class="ves-kicker">New customer</span>
        <h1 class="ves-serif ves-auth-title">Create your account</h1>
        <p class="ves-auth-text">
            Set up your account to track orders, save details, and checkout faster.
        </p>
    </div>

    <form class="ves-auth-form"
          method="POST"
          action="{{ route('register') }}">
        @csrf

                @if (! empty($redirectTo))
                        <input name="redirect_to"
                                     type="hidden"
                                     value="{{ $redirectTo }}">
                @endif

        <label class="ves-auth-field">
            <span>Full name</span>
            <input name="name"
                   type="text"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   autocomplete="name">
        </label>
        @error('name')
            <p class="ves-auth-error">{{ $message }}</p>
        @enderror

        <label class="ves-auth-field">
            <span>Email address</span>
            <input name="email"
                   type="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="email">
        </label>
        @error('email')
            <p class="ves-auth-error">{{ $message }}</p>
        @enderror

        <label class="ves-auth-field">
            <span>Password</span>
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
            Create account
        </button>
    </form>

    <a class="ves-link ves-auth-secondary-link"
       href="{{ route('login', ! empty($redirectTo) ? ['redirect_to' => $redirectTo] : []) }}">
        Already have an account? Sign in
    </a>
</x-layouts.auth>
