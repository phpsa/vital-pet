<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Extensions;

use App\Support\StorefrontCountry;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Lunar\Admin\Support\Extending\BaseExtension;

final class OrderListExtension extends BaseExtension
{
    public function extendTable(Table $table): Table
    {
        $enabledIso2s = StorefrontCountry::enabledIso2s();

        $countryQuery = \Lunar\Models\Country::whereExists(function ($query) {
            $addressTable = (new \Lunar\Models\OrderAddress)->getTable();
            $countryTable = (new \Lunar\Models\Country)->getTable();

            $query->selectRaw(1)
                ->from($addressTable)
                ->whereColumn("{$addressTable}.country_id", "{$countryTable}.id")
                ->where("{$addressTable}.type", 'shipping');
        });

        if (! empty($enabledIso2s)) {
            $countryQuery->whereIn('iso2', $enabledIso2s);
        }

        $countryOptions = $countryQuery->orderBy('name')->pluck('name', 'id')->toArray();

        $table->pushFilters([
            SelectFilter::make('shipping_country')
                ->label('Shipping Country')
                ->options($countryOptions)
                ->query(function ($query, array $data): void {
                    if (! empty($data['value'])) {
                        $query->whereHas('shippingAddress', function ($q) use ($data): void {
                            $q->where('country_id', $data['value']);
                        });
                    }
                }),
        ]);

        return $table;
    }
}
