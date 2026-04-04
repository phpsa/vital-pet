<?php

namespace App\Livewire\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Facades\StorefrontSession;
use Lunar\Models\Product;

class LatestProducts extends Component
{
    use WithPagination;

    public function getProductsProperty(): LengthAwarePaginator
    {
        return Product::customerGroup(StorefrontSession::getCustomerGroups())
            ->with([
            'defaultUrl',
            'thumbnail',
            'variants.basePrices.currency',
        ])
        ->latest()
        ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.components.latest-products');
    }
}
