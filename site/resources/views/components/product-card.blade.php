@props([
    'product',
    'collectionSlug' => null,
])

<a class="block group ves-product-card"
   href="{{ $collectionSlug
       ? route('product.view', ['slug' => $product->defaultUrl->slug, 'fromCollection' => $collectionSlug])
       : route('product.view', $product->defaultUrl->slug) }}"
   wire:navigate
>
    <div class="overflow-hidden aspect-w-1 aspect-h-1">
        @if ($product->thumbnail)
            <img class="object-cover transition-transform duration-300 group-hover:scale-105"
                 src="{{ $product->thumbnail->getUrl('medium') }}"
                 alt="{{ $product->translateAttribute('name') }}" />
        @endif
    </div>

    <div class="ves-product-meta">
        <strong class="ves-product-name">
            {{ $product->translateAttribute('name') }}
        </strong>

        <p class="ves-product-price">
            <span class="sr-only">
                Price
            </span>

            <x-product-price :product="$product" />
        </p>
    </div>
</a>
