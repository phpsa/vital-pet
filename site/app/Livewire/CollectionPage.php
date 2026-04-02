<?php

namespace App\Livewire;

use App\Traits\FetchesUrls;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Models\Collection as CollectionModel;

class CollectionPage extends Component
{
    use FetchesUrls;

    protected $queryString = [
        'sort' => ['except' => 'default'],
    ];

    public string $sort = 'default';

    public function mount(string $slug): void
    {
        $this->url = $this->fetchUrl(
            $slug,
            (new CollectionModel)->getMorphClass(),
            [
                'element.thumbnail',
                'element.products.variants.basePrices',
                'element.products.defaultUrl',
            ]
        );

        if (! $this->url) {
            abort(404);
        }
    }

    /**
     * Computed property to return the collection.
     */
    public function getCollectionProperty(): mixed
    {
        return $this->url->element;
    }

    /**
     * Computed property to return sorted products.
     */
    public function getProductsProperty(): Collection
    {
        $products = $this->collection->products;

        return match ($this->sort) {
            'price_low' => $products->sortBy(fn ($product) => $this->getSortablePrice($product))->values(),
            'price_high' => $products->sortByDesc(fn ($product) => $this->getSortablePrice($product))->values(),
            'name' => $products->sortBy(fn ($product) => (string) $product->translateAttribute('name'))->values(),
            default => $products,
        };
    }

    protected function getSortablePrice($product): int
    {
        $prices = $product->variants
            ->flatMap(fn ($variant) => $variant->basePrices)
            ->map(fn ($basePrice) => $basePrice->price?->value)
            ->filter(fn ($price) => is_numeric($price));

        return $prices->isNotEmpty() ? (int) $prices->min() : \PHP_INT_MAX;
    }

    public function render(): View
    {
        return view('livewire.collection-page')
            ->title($this->collection->translateAttribute('name'));
    }
}
