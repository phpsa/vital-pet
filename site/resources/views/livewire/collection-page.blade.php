<section class="ves-catalog-wrap">
    @php
        $browseCollections = $this->collection->parent ? $this->collection->parent->children : $this->collection->children;

        if ($browseCollections->isEmpty()) {
            $browseCollections = collect([$this->collection]);
        }
    @endphp

    <div class="ves-catalog-grid">
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

        <div>
            <div class="ves-catalog-header">
                <h1 class="ves-catalog-title">
                    {{ $this->collection->translateAttribute('name') }}
                </h1>

                <div class="ves-catalog-controls">
                    <form class="ves-catalog-search"
                          action="{{ route('search.view') }}">
                        <button type="submit"
                                aria-label="Search products">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-4 h-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>

                        <input type="search"
                               name="term"
                               placeholder="Search products" />
                    </form>

                    <div class="ves-catalog-sort">
                        <span>Sort by:</span>
                        <select>
                            <option selected>Default</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Name</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="ves-catalog-products">
                @forelse($this->collection->products as $product)
                    <x-product-card :product="$product" />
                @empty
                @endforelse
            </div>
        </div>
    </div>
</section>
