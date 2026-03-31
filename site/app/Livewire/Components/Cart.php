<?php

namespace App\Livewire\Components;

use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
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
        $countryId = $this->inventoryService()->resolveCountryIdForCart($this->cart);
        $requestLines = collect($this->lines)->map(function ($line) use ($lineModelsById) {
            $lineModel = $lineModelsById->get($line['id']);

            if (! $lineModel) {
                return null;
            }

            return [
                'purchasable_type' => $lineModel->purchasable_type,
                'purchasable_id' => $lineModel->purchasable_id,
                'quantity' => (int) $line['quantity'],
            ];
        })->filter();

        $requestedByPurchasable = $this->inventoryService()->requestedByPurchasable($requestLines);

        foreach ($this->lines as $index => $line) {
            $lineModel = $lineModelsById->get($line['id']);

            if (! $lineModel) {
                continue;
            }

            $purchasable = $lineModel->purchasable;
            $purchasableKey = $lineModel->purchasable_type.':'.$lineModel->purchasable_id;
            $requestedQuantity = (int) ($requestedByPurchasable[$purchasableKey] ?? 0);
            $availableQuantity = $this->inventoryService()->availableQuantityForPurchasable($purchasable, $countryId);

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
        $countryId = $this->inventoryService()->resolveCountryIdForCart($this->cart);

        $this->lines = $this->cartLines->map(function ($line) use ($countryId) {
            $isOnBackorder = false;
            $isOutOfStock = false;

            if ($line->purchasable) {
                $available = $this->inventoryService()->availableQuantityForPurchasable($line->purchasable, $countryId);
                $isOutOfStock = $available <= 0;

                if (!$isOutOfStock && $line->purchasable->purchasable === 'in_stock_or_on_backorder') {
                    $inStock = $this->inventoryService()->inStockQuantityForPurchasable($line->purchasable, $countryId);
                    $isOnBackorder = (int) $line->quantity > $inStock;
                }
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
                'out_of_stock' => $isOutOfStock,
            ];
        })->toArray();
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

    protected function inventoryService(): InventoryService
    {
        return app(InventoryService::class);
    }

    protected function isStockManagedLine($line): bool
    {
        $type = (string) ($line->purchasable_type ?? '');

        return $type !== '' && is_subclass_of($type, Model::class);
    }
}
