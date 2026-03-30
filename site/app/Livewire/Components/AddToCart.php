<?php

namespace App\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Lunar\Base\Purchasable;
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

        // Check purchasability tier
        if ($this->purchasable->purchasable === 'always') {
            // No stock restrictions
            CartSession::manager()->add($this->purchasable, $this->quantity);
        } elseif ($this->purchasable->purchasable === 'in_stock') {
            // Must have sufficient stock available
            $availableStock = $this->purchasable->stock;

            if ($availableStock === 0) {
                $this->addError('quantity', 'This item is out of stock.');
                return;
            }

            if ($availableStock < $requestedQuantity) {
                $this->addError(
                    'quantity',
                    "Only {$availableStock} item(s) in stock. You already have {$requestedQuantity} requested in your cart."
                );
                return;
            }

            CartSession::manager()->add($this->purchasable, $this->quantity);
        } elseif ($this->purchasable->purchasable === 'in_stock_or_on_backorder') {
            // Check combined stock + backorder availability
            $availableStock = $this->purchasable->stock;
            $availableBackorder = $this->purchasable->backorder;
            $totalAvailable = $availableStock + $availableBackorder;

            if ($totalAvailable === 0) {
                $this->addError('quantity', 'This item is out of stock and not available for backorder.');
                return;
            }

            if ($totalAvailable < $requestedQuantity) {
                $inStockText = $availableStock > 0 ? "{$availableStock} in stock" : 'none in stock';
                $backorderText = $availableBackorder > 0 ? "{$availableBackorder} on backorder" : 'none on backorder';
                $this->addError(
                    'quantity',
                    "Only {$totalAvailable} item(s) available ({$inStockText}, {$backorderText}). You already have {$requestedQuantity} requested in your cart."
                );
                return;
            }

            CartSession::manager()->add($this->purchasable, $this->quantity);
        } else {
            $this->addError('quantity', 'This item cannot be purchased at this time.');
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
}
