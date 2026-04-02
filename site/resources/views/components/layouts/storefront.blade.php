<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    @php
        $seo = app(\App\Settings\ContentSettings::class);
        $siteTitle = $seo->site_title ?: config('app.name');
        $pageTitle = isset($title) && (string) $title !== '' ? (string) $title . ' — ' . $siteTitle : $siteTitle;
    @endphp
    <title>{{ $pageTitle }}</title>
    @if ($seo->meta_description)
        <meta name="description" content="{{ $seo->meta_description }}">
    @endif
    @if ($seo->meta_keywords)
        <meta name="keywords" content="{{ $seo->meta_keywords }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link
        href="{{ asset('css/theme.css') }}"
        rel="stylesheet"
    >

    <link
        rel="icon"
        href="{{ asset('favicon.svg') }}"
    >
    @livewireStyles
</head>

<body class="ves-theme antialiased @if(\App\Support\TemplateHelper::isPetstore()) ves-petstore @endif">
    <x-announcement-banner />
    @livewire('components.navigation')

    <main>
        {{ $slot }}
    </main>

    <x-footer />

    @livewireScripts
</body>

</html>
