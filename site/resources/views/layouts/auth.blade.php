<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>{{ $title ?? 'Account' }}</title>
    <meta
        name="description"
        content="Customer account access for the Vital storefront."
    >
    <link
        href="{{ asset('css/app.css') }}"
        rel="stylesheet"
    >
    <link
        href="{{ asset('css/theme.css') }}"
        rel="stylesheet"
    >
    <link
        rel="icon"
        href="{{ asset('favicon.svg') }}"
    >
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
</head>

<body class="ves-theme antialiased">
    <header class="relative border-b border-gray-100 ves-nav">
        <div class="flex items-center justify-between h-16 px-4 mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
            <a class="flex items-center flex-shrink-0"
               href="{{ url('/') }}">
                <span class="sr-only">Home</span>

                <x-brand.logo class="w-auto h-6 ves-logo" />
            </a>

            <a class="text-sm font-medium transition hover:opacity-75 ves-nav-link"
               href="{{ url('/') }}">
                Back to store
            </a>
        </div>
    </header>

    <main class="ves-auth-shell">
        <section class="ves-auth-card ves-panel">
            {{ $slot }}
        </section>
    </main>

    <x-footer />
</body>

</html>