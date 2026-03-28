<section class="ves-catalog-wrap">
    @php
        $breadcrumbItems = [
            ['label' => 'Home', 'url' => url('/')],
        ];

        if ($this->collection->parent && $this->collection->parent->defaultUrl) {
            $breadcrumbItems[] = [
                'label' => $this->collection->parent->translateAttribute('name'),
                'url' => route('collection.view', $this->collection->parent->defaultUrl->slug),
            ];
        }

        $breadcrumbItems[] = [
            'label' => $this->collection->translateAttribute('name'),
            'url' => null,
        ];
    @endphp

    <x-breadcrumbs :items="$breadcrumbItems" />

    @php
        $browseCollections = $this->collection->children;
        $hasBrowseSidebar = $browseCollections->isNotEmpty();
    @endphp

    <div class="ves-catalog-grid {{ $hasBrowseSidebar ? '' : 'ves-no-sidebar' }}">
        @if ($hasBrowseSidebar)
            <aside class="ves-catalog-sidebar">
                <h2 class="ves-serif">Browse by</h2>

                <ul>
                    @foreach ($browseCollections as $browseCollection)
                        @php
                            $isCurrent = $browseCollection->id === $this->collection->id;
                        @endphp

                        <li>
                            <a class="{{ $isCurrent ? 'ves-current' : '' }}"
                               href="{{ route('collection.view', $browseCollection->defaultUrl->slug) }}"
                               wire:navigate>
                                {{ $browseCollection->translateAttribute('name') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif

        <div>
            <div class="ves-catalog-header">
                <h1 class="ves-catalog-title">
                    {{ $this->collection->translateAttribute('name') }}
                </h1>

                <div class="ves-catalog-controls">
                    <div class="ves-catalog-sort">
                        <span>Sort by:</span>
                        <select wire:model.live="sort">
                            <option value="default">Default</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="name">Name</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="ves-catalog-products">
                @forelse($this->products as $product)
                    <x-product-card :product="$product"
                                    :collectionSlug="$this->collection->defaultUrl?->slug" />
                @empty
                @endforelse
            </div>
        </div>
    </div>
</section>
