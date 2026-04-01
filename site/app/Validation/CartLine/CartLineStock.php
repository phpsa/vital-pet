<?php

namespace App\Validation\CartLine;

use App\Services\InventoryService;
use App\Support\StorefrontCountry;
use Lunar\Validation\BaseValidator;

class CartLineStock extends BaseValidator
{
    public function validate(): bool
    {
        $quantity = $this->parameters['quantity'] ?? 0;
        $purchasable = $this->parameters['purchasable'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $cart = $this->parameters['cart'] ?? null;

        if ($cartLineId && ! $purchasable && $cart) {
            $purchasable = $cart->lines->first(
                fn ($cartLine) => $cartLine->id == $cartLineId
            )?->purchasable;
        }

        $countryId = StorefrontCountry::id();

        $available = app(InventoryService::class)->availableQuantityForPurchasable($purchasable, $countryId);

        return $quantity <= $available
            ? $this->pass()
            : $this->fail('cart', 'Item is not available at this quantity.');
    }
}
