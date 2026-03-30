<x-layouts.auth title="Login">
    <div class="ves-auth-copy">
        <span class="ves-kicker">Account access</span>
        <h1 class="ves-serif ves-auth-title">Sign in</h1>
        <p class="ves-auth-text">
            Access your account to manage orders, update details, and continue checkout.
        </p>
    </div>

    @if (session('status'))
        <div class="ves-auth-alert ves-auth-alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form class="ves-auth-form"
          method="POST"
          action="{{ route('login') }}">
        @csrf

                @if (! empty($redirectTo))
                        <input name="redirect_to"
                                     type="hidden"
                                     value="{{ $redirectTo }}">
                @endif

        <label class="ves-auth-field">
            <span>Email address</span>
            <input name="email"
                   type="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
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
                   autocomplete="current-password">
        </label>
        @error('password')
            <p class="ves-auth-error">{{ $message }}</p>
        @enderror

        <div class="ves-auth-row">
            <label class="ves-auth-checkbox">
                <input name="remember"
                       type="checkbox"
                       value="1"
                       {{ old('remember') ? 'checked' : '' }}>
                <span>Remember me</span>
            </label>

            <a class="ves-link"
               href="{{ route('password.request', ! empty($redirectTo) ? ['redirect_to' => $redirectTo] : []) }}">
                Forgot password?
            </a>
        </div>

        <button class="ves-button ves-button-primary ves-auth-submit"
                type="submit">
            Sign in
        </button>
    </form>

    @if (! config('template.storefront_requires_auth'))
        <a class="ves-link ves-auth-secondary-link"
           href="{{ route('register', ! empty($redirectTo) ? ['redirect_to' => $redirectTo] : []) }}">
            Need an account? Register
        </a>
    @endif
</x-layouts.auth>