<div>
    <div class="ves-catalog-products">
        @foreach ($this->products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>

    @if ($this->products->hasPages())
        <div class="mt-8">
            {{ $this->products->links() }}
        </div>
    @endif
</div>
