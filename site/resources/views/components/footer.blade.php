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

        <p class="pt-4 mt-4 text-sm text-gray-500 border-t border-gray-100">
            &copy; {{ now()->year }} Vital
        </p>
    </div>
</footer>
