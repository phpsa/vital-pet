<?php

namespace App\Livewire\Components;

use App\Services\InventoryService;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Base\Purchasable;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\CartSession;

class AddToCart extends Component
{
    /**
     * The purchasable model we want to add to the cart.
     */
    public ?Purchasable $purchasable = null;

    /**
     * The quantity to add to cart.
     */
    public int $quantity = 1;

    public function rules(): array
    {
        return [
            'quantity' => 'required|numeric|min:1|max:10000',
        ];
    }

    public function addToCart(): void
    {
        $this->validate();

        $requestedQuantity = $this->quantity + $this->existingQuantityInCart();

        $countryId = $this->inventoryService()->resolveCountryIdForCart(CartSession::current());
        $validation = $this->inventoryService()->validateRequestedQuantity(
            $this->purchasable,
            $requestedQuantity,
            $countryId
        );

        if (! $validation['ok']) {
            if ($validation['available'] <= 0) {
                $this->addError('quantity', 'This item is out of stock.');
            } else {
                $this->addError(
                    'quantity',
                    "Only {$validation['available']} item(s) are available. You already have {$requestedQuantity} requested in your cart."
                );
            }

            return;
        }

        if (! in_array($this->purchasable->purchasable, ['always', 'in_stock', 'in_stock_or_on_backorder'], true)) {
            $this->addError('quantity', 'This item cannot be purchased at this time.');

            return;
        }

        try {
            CartSession::manager()->add($this->purchasable, $this->quantity);
        } catch (CartException $e) {
            $this->addError('quantity', $e->getMessage() ?: 'This item cannot be added to the cart.');

            return;
        }

        $this->dispatch('add-to-cart');
    }

    protected function existingQuantityInCart(): int
    {
        $cart = CartSession::current();

        if (! $cart) {
            return 0;
        }

        return (int) $cart->lines->filter(function ($line) {
            return $line->purchasable_type === $this->purchasable->getMorphClass()
                && (int) $line->purchasable_id === (int) $this->purchasable->id;
        })->sum('quantity');
    }

    public function render(): View
    {
        return view('livewire.components.add-to-cart');
    }

    protected function inventoryService(): InventoryService
    {
        return app(InventoryService::class);
    }
}
