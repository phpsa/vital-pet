<x-layouts.auth title="Forgot Password">
    <div class="ves-auth-copy">
        <span class="ves-kicker">Password help</span>
        <h1 class="ves-serif ves-auth-title">Forgot your password?</h1>
        <p class="ves-auth-text">
            Enter your email address and we will send you a secure reset link.
        </p>
    </div>

    @if (session('status'))
        <div class="ves-auth-alert ves-auth-alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form class="ves-auth-form"
          method="POST"
          action="{{ route('password.email') }}">
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

        <button class="ves-button ves-button-primary ves-auth-submit"
                type="submit">
            Email reset link
        </button>
    </form>

    <a class="ves-link ves-auth-secondary-link"
       href="{{ route('login', ! empty($redirectTo) ? ['redirect_to' => $redirectTo] : []) }}">
        Back to sign in
    </a>
</x-layouts.auth>