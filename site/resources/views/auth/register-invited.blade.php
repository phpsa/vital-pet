<x-layouts.auth title="Accept Invitation">
    <div class="ves-auth-copy">
        <span class="ves-kicker">You've been invited</span>
        <h1 class="ves-serif ves-auth-title">Create your account</h1>
        <p class="ves-auth-text">
            Complete the form below to accept your invitation and get started.
        </p>
    </div>

    <form class="ves-auth-form"
          method="POST"
          action="{{ route('register.invited.store', ['token' => $invitation->token]) }}">
        @csrf

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
                   value="{{ old('email', $invitation->email) }}"
                   required
                   autocomplete="email">
        </label>
        @error('email')
            <p class="ves-auth-error">{{ $message }}</p>
        @enderror

        @if (! empty($showCountrySelector))
            @php
                $regCountriesJson = collect($countries)->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values();
                $regDefaultId = old('country_id', $defaultCountryId);
            @endphp
            <script>
                window.__invCountries = @json($regCountriesJson);
                window.__invDefault   = '{{ $regDefaultId }}';
                window.invCountryDropdown = function() {
                    return {
                        open: false,
                        selected: window.__invDefault || '',
                        countries: window.__invCountries || [],
                        label: '',
                        init: function() { this.syncLabel(); },
                        syncLabel: function() {
                            var sel = String(this.selected);
                            var m = this.countries.find(function(c) { return String(c.id) === sel; });
                            this.label = m ? m.name : '';
                        },
                        choose: function(id) {
                            this.selected = String(id);
                            this.open = false;
                            this.syncLabel();
                        }
                    };
                };
            </script>
            <div class="ves-auth-field"
                 x-data="invCountryDropdown()"
                 @click.outside="open = false">

                <input type="hidden" name="country_id" :value="selected" required>
                <span>Country</span>

                <div class="relative">
                    <button type="button"
                            @click="open = !open"
                            class="ves-auth-listbox-btn">
                        <span x-text="label"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ves-auth-listbox-icon" :class="open ? 'rotate-180' : ''">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="ves-auth-listbox-panel">
                        <template x-for="country in countries" :key="country.id">
                            <button type="button"
                                    @click="choose(country.id)"
                                    class="ves-auth-listbox-option"
                                    :class="String(country.id) == String(selected) ? 'font-semibold' : 'font-normal'">
                                <span x-text="country.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            @error('country_id')
                <p class="ves-auth-error">{{ $message }}</p>
            @enderror
        @endif

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
</x-layouts.auth>
