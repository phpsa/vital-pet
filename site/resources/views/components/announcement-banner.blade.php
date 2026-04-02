@php
    $bannerText = app(\App\Settings\ContentSettings::class)->banner_text;
@endphp

@if ($bannerText)
    <div class="ves-announcement-bar text-sm font-semibold text-center py-2 px-4 tracking-wide uppercase">
        {{ $bannerText }}
    </div>
@endif
