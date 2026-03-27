<div>
    <div class="ves-home-wrap">
        <x-welcome-banner />

        @if ($this->saleCollection)
            <section id="featured">
                <div class="ves-section-title">
                    <h2 class="ves-serif">
                        {{ $this->saleCollection->translateAttribute('name') }}
                    </h2>

                    <span class="ves-kicker">Special Offers</span>
                </div>

                <div class="ves-product-grid">
                    @foreach ($this->saleCollection->products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
