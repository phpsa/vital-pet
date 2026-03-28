<?php

namespace Vital\Airwallex\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Models\Cart;

class AirwallexPaymentIntent extends BaseModel
{
    protected $guarded = [];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::modelClass(), 'cart_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        $final = config('lunar.airwallex.final_statuses', ['SUCCEEDED', 'FAILED', 'CANCELLED']);

        return $query->whereNotIn('status', $final);
    }

    public function isActive(): bool
    {
        $final = config('lunar.airwallex.final_statuses', ['SUCCEEDED', 'FAILED', 'CANCELLED']);

        return $this->status && ! in_array($this->status, $final, true);
    }
}
