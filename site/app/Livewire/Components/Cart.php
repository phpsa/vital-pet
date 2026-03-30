<?php

namespace App\Livewire\Components;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;

class Cart extends Component
{
    /**
     * The editable cart lines.
     */
    public array $lines;

    public bool $linesVisible = false;

    protected $listeners = [
        'add-to-cart' => 'handleAddToCart',
    ];

    public function rules(): array
    {
        return [
            'lines.*.quantity' => 'required|numeric|min:1|max:10000',
        ];
    }

    public function mount(): void
    {
        $this->mapLines();
    }

    /**
     * Get the current cart instance.
     */
    public function getCartProperty()
    {
        return CartSession::current();
    }

    /**
     * Return the cart lines from the cart.
     */
    public function getCartLinesProperty(): Collection
    {
        return $this->cart->lines ?? collect();
    }

    /**
     * Update the cart lines.
     */
    public function updateLines(): void
    {
        $this->validate();

        $this->resetErrorBag();

        $lineModelsById = $this->cartLines->keyBy('id');
        $requestedByPurchasable = [];

        foreach ($this->lines as $line) {
            $lineModel = $lineModelsById->get($line['id']);

            if (! $lineModel) {
                continue;
            }

            $purchasableKey = $lineModel->purchasable_type.':'.$lineModel->purchasable_id;
            $requestedByPurchasable[$purchasableKey] = ($requestedByPurchasable[$purchasableKey] ?? 0) + (int) $line['quantity'];
        }

        foreach ($this->lines as $index => $line) {
            $lineModel = $lineModelsById->get($line['id']);

            if (! $lineModel) {
                continue;
            }

            $purchasable = $lineModel->purchasable;
            $purchasableKey = $lineModel->purchasable_type.':'.$lineModel->purchasable_id;
            $requestedQuantity = (int) ($requestedByPurchasable[$purchasableKey] ?? 0);
            $availableQuantity = $this->availableQuantity($purchasable);

            if ($requestedQuantity > $availableQuantity) {
                $this->addError(
                    'lines.'.$index.'.quantity',
                    "Only {$availableQuantity} item(s) are available for this product."
                );
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        CartSession::updateLines(
            collect($this->lines)
        );
        $this->mapLines();
        $this->dispatch('cartUpdated');
    }

    public function removeLine($id): void
    {
        CartSession::remove($id);
        $this->mapLines();
    }

    /**
     * Map the cart lines.
     *
     * We want to map out our cart lines like this so we can
     * add some validation rules and make them editable.
     */
    public function mapLines(): void
    {
        $this->lines = $this->cartLines->map(function ($line) {
            $isOnBackorder = false;

            if ($line->purchasable && $line->purchasable->purchasable === 'in_stock_or_on_backorder') {
                $stock = max(0, (int) $line->purchasable->stock);
                $isOnBackorder = (int) $line->quantity > $stock;
            }

            return [
                'id' => $line->id,
                'identifier' => $line->purchasable->getIdentifier(),
                'quantity' => $line->quantity,
                'description' => $line->purchasable->getDescription(),
                'thumbnail' => $line->purchasable->getThumbnail()?->getUrl(),
                'option' => $line->purchasable->getOption(),
                'options' => $line->purchasable->getOptions()->implode(' / '),
                'sub_total' => $line->subTotal->formatted(),
                'unit_price' => $line->unitPrice->formatted(),
                'on_backorder' => $isOnBackorder,
            ];
        })->toArray();
    }

    protected function availableQuantity($purchasable): int
    {
        if (! $purchasable) {
            return 0;
        }

        return match ($purchasable->purchasable) {
            'always' => PHP_INT_MAX,
            'in_stock' => max(0, (int) $purchasable->stock),
            'in_stock_or_on_backorder' => max(0, (int) $purchasable->stock + (int) $purchasable->backorder),
            default => 0,
        };
    }

    public function handleAddToCart(): void
    {
        $this->mapLines();
        $this->linesVisible = true;
    }

    public function render(): View
    {
        return view('livewire.components.cart');
    }
}
