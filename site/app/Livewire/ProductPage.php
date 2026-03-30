<?php

namespace App\Livewire;

use App\Traits\FetchesUrls;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductPage extends Component
{
    use FetchesUrls;

    protected $queryString = [
        'fromCollection',
    ];

    public ?string $fromCollection = null;

    /**
     * The selected option values.
     */
    public array $selectedOptionValues = [];

    public function mount($slug): void
    {
        $this->url = $this->fetchUrl(
            $slug,
            (new Product)->getMorphClass(),
            [
                'element.media',
                'element.collections.defaultUrl',
                'element.variants.basePrices.currency',
                'element.variants.basePrices.priceable',
                'element.variants.values.option',
            ]
        );

        if (! $this->url) {
            abort(404);
        }

        $this->selectedOptionValues = $this->productOptions->mapWithKeys(function ($data) {
            return [$data['option']->id => $data['values']->first()->id];
        })->toArray();
    }

    /**
     * Computed property to get variant.
     */
    public function getVariantProperty(): ProductVariant
    {
        return $this->product->variants->first(function ($variant) {
            return ! $variant->values->pluck('id')
                ->diff(
                    collect($this->selectedOptionValues)->values()
                )->count();
        });
    }

    /**
     * Computed property to return all available option values.
     */
    public function getProductOptionValuesProperty(): Collection
    {
        return $this->product->variants->pluck('values')->flatten();
    }

    /**
     * Computed propert to get available product options with values.
     */
    public function getProductOptionsProperty(): Collection
    {
        return $this->productOptionValues->unique('id')->groupBy('product_option_id')
            ->map(function ($values) {
                return [
                    'option' => $values->first()->option,
                    'values' => $values,
                ];
            })->values();
    }

    /**
     * Computed property to return product.
     */
    public function getProductProperty(): Product
    {
        return $this->url->element;
    }

    /**
     * Return all images for the product.
     */
    public function getImagesProperty(): Collection
    {
        return $this->product->media->sortBy('order_column');
    }

    /**
     * Computed property to return current image.
     */
    public function getImageProperty(): ?Media
    {
        if (count($this->variant->images)) {
            return $this->variant->images->first();
        }

        if ($primary = $this->images->first(fn ($media) => $media->getCustomProperty('primary'))) {
            return $primary;
        }

        return $this->images->first();
    }

    /**
     * Get the stock status text for display on product page.
     */
    public function getStockStatusProperty(): ?string
    {
        if ($this->variant->purchasable === 'always') {
            return '5+ in Stock';
        }

        if ($this->variant->purchasable === 'in_stock') {
            $stock = max(0, (int) $this->variant->stock);

            if ($stock <= 0) {
                return 'Out of Stock';
            }

            return $stock >= 5 ? '5+ in Stock' : "{$stock} in Stock";
        }

        if ($this->variant->purchasable === 'in_stock_or_on_backorder') {
            $stock = max(0, (int) $this->variant->stock);
            $backorder = max(0, (int) $this->variant->backorder);

            if ($stock <= 0 && $backorder <= 0) {
                return 'Out of Stock';
            }

            $stockText = '';
            if ($stock > 0) {
                $stockText = $stock >= 5 ? '5+ in Stock' : "{$stock} in Stock";
            }

            $backorderText = '';
            if ($backorder > 0) {
                $backorderText = $backorder >= 5 ? '5+ on Backorder' : "{$backorder} on Backorder";
            }

            if ($stockText && $backorderText) {
                return "{$stockText} / {$backorderText}";
            }

            return $stockText ?: $backorderText;
        }

        return null;
    }

    public function getStockStatusToneProperty(): string
    {
        if ($this->variant->purchasable === 'always') {
            return 'success';
        }

        if ($this->variant->purchasable === 'in_stock') {
            return (int) $this->variant->stock > 0 ? 'success' : 'danger';
        }

        if ($this->variant->purchasable === 'in_stock_or_on_backorder') {
            $stock = max(0, (int) $this->variant->stock);
            $backorder = max(0, (int) $this->variant->backorder);

            if ($stock <= 0 && $backorder <= 0) {
                return 'danger';
            }

            return $backorder > 0 ? 'warning' : 'success';
        }

        return 'danger';
    }

    public function render(): View
    {
        return view('livewire.product-page');
    }
}
