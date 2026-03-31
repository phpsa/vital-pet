<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Country;
use Lunar\Models\ProductVariant;

class ProductVariantRegionalStock extends Model
{
    protected $guarded = [];

    protected $casts = [
        'stock' => 'int',
        'backorder' => 'int',
    ];

    public function getTable(): string
    {
        return config('lunar.database.table_prefix').'product_variant_regional_stocks';
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
