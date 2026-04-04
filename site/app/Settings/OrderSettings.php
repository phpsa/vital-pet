<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class OrderSettings extends Settings
{
    public float $min_order_value = 0;

    /** @var string[] */
    public array $storefront_country_iso2 = ['AU', 'NZ'];

    public static function group(): string
    {
        return 'order';
    }
}
