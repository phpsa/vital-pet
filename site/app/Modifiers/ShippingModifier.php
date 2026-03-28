<?php

namespace App\Modifiers;

use Lunar\Models\Cart;

class ShippingModifier
{
    public function handle(Cart $cart, \Closure $next)
    {
        return $next($cart);
    }
}
