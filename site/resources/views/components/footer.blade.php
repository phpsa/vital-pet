<footer class="bg-gray-50 ves-footer">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-brand.logo class="w-auto h-8 ves-logo" />

        <p class="max-w-sm mt-4 text-gray-700">
            Premium dog accessories, engaging toys, and nutritious treats curated for
            active adventures, training moments, and everyday joy with your best friend.
        </p>

        <p class="mt-3 text-xs tracking-[0.18em] uppercase text-gray-400">
            Template: {{ config('template.active') }}
        </p>

        @php
            $countrySelectorEnabled = \App\Support\StorefrontCountry::isEnabled();
            $footerCountries = \App\Support\StorefrontCountry::allowedCountries();
            $activeCountryId = \App\Support\StorefrontCountry::id();
        @endphp

        @if ($countrySelectorEnabled && $footerCountries->count() > 0)
            @php
                $footerCountriesJson = $footerCountries->map(fn($c) => ['id' => $c['id'], 'name' => $c['name']])->values();
            @endphp
            <script>
                window.__footerCountries = @json($footerCountriesJson);
                window.__footerSelected  = {{ (int) $activeCountryId }};
                window.footerCountryDropdown = function() {
                    return {
                        open: false,
                        selected: window.__footerSelected || 0,
                        countries: window.__footerCountries || [],
                        label: '',
                        init: function() { this.syncLabel(); },
                        syncLabel: function() {
                            var sel = Number(this.selected);
                            var m = this.countries.find(function(c) { return Number(c.id) === sel; });
                            this.label = m ? m.name : '';
                        },
                        choose: function(id) {
                            this.selected = id;
                            this.open = false;
                            this.syncLabel();
                            this.$nextTick(function() { this.$refs.form.submit(); }.bind(this));
                        }
                    };
                };
            </script>
            <div class="mt-6 max-w-xs"
                 x-data="footerCountryDropdown()"
                 @click.outside="open = false">

                <form method="POST" action="{{ route('country.switch') }}" x-ref="form">
                    @csrf
                    <input type="hidden" name="country_id" :value="selected">
                </form>

                <p class="mb-1 text-xs font-medium tracking-wide uppercase text-gray-500">Ship to</p>

                <div class="relative">
                    <button type="button"
                            @click="open = !open"
                            class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-800 shadow-sm transition-colors hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <span x-text="label"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ml-2 h-4 w-4 shrink-0 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''">
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
                         class="absolute left-0 z-20 mt-1 w-full origin-top-left overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                        <template x-for="country in countries" :key="country.id">
                            <button type="button"
                                    @click="choose(country.id)"
                                    class="flex w-full items-center px-3 py-2 text-sm text-gray-800 hover:bg-gray-100"
                                    :class="country.id == selected ? 'font-semibold' : 'font-normal'">
                                <span x-text="country.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        @endif

        <p class="pt-4 mt-4 text-sm text-gray-500 border-t border-gray-100">
            &copy; {{ now()->year }} Vital
        </p>
    </div>
</footer>
