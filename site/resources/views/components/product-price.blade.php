@php
    $currentPrice = $price?->price;
    $comparePrice = $price?->compare_price;
    $hasComparePrice = $currentPrice && $comparePrice && $comparePrice->value > $currentPrice->value;
@endphp

<span {{ $attributes->class('inline-flex items-center gap-2') }}>
    @if ($hasComparePrice)
        <span class="ves-price-compare">
            {{ $comparePrice->formatted() }}
        </span>
    @endif

    <span class="ves-price-current">
        {{ $currentPrice?->formatted() }}
    </span>
</span>
