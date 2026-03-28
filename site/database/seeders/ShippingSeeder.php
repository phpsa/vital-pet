<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currency = Currency::getDefault();

        $nzShipping = ShippingMethod::query()->where('code', 'NZL')
            ->orWhere('code', 'STNDRD')
            ->first() ?: new ShippingMethod;

        $nzShipping->fill([
            'name' => 'New Zealand Shipping',
            'code' => 'NZL',
            'enabled' => true,
            'driver' => 'ship-by',
            'data' => [
                'charge_by' => 'cart_total',
            ],
        ]);
        $nzShipping->save();

        $nzShippingZone = ShippingZone::query()->firstOrCreate([
            'name' => 'New Zealand',
            'type' => 'countries',
        ]);

        $oldNzRates = ShippingRate::query()
            ->where('shipping_zone_id', $nzShippingZone->id)
            ->where('shipping_method_id', '!=', $nzShipping->id)
            ->pluck('id');

        if ($oldNzRates->isNotEmpty()) {
            Price::query()
                ->where('priceable_type', (new ShippingRate)->getMorphClass())
                ->whereIn('priceable_id', $oldNzRates)
                ->delete();

            ShippingRate::query()->whereIn('id', $oldNzRates)->delete();
        }

        $nzShippingRate = ShippingRate::query()->updateOrCreate([
            'shipping_zone_id' => $nzShippingZone->id,
            'shipping_method_id' => $nzShipping->id,
        ], [
            'enabled' => true,
        ]);

        $nzShippingZone->countries()->sync(
            Country::where('iso3', '=', 'NZL')->first()->id,
        );

        Price::query()
            ->where('priceable_type', (new ShippingRate)->getMorphClass())
            ->where('priceable_id', $nzShippingRate->id)
            ->delete();

        Price::create([
            'priceable_type' => (new ShippingRate)->getMorphClass(),
            'priceable_id' => $nzShippingRate->id,
            'price' => 1200,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
        ]);

        // Free shipping on orders over 100.00
        Price::create([
            'priceable_type' => (new ShippingRate)->getMorphClass(),
            'priceable_id' => $nzShippingRate->id,
            'price' => 0,
            'min_quantity' => 10000,
            'currency_id' => $currency->id,
        ]);

        // Australia shipping

        $auShipping = ShippingMethod::query()->updateOrCreate([
            'code' => 'AUS',
        ], [
            'name' => 'Australia Shipping',
            'enabled' => true,
            'driver' => 'ship-by',
            'data' => [
                'charge_by' => 'cart_total',
            ],
        ]);

        $auShippingZone = ShippingZone::query()->firstOrCreate([
            'name' => 'Australia',
            'type' => 'countries',
        ]);

        $oldAuRates = ShippingRate::query()
            ->where('shipping_zone_id', $auShippingZone->id)
            ->where('shipping_method_id', '!=', $auShipping->id)
            ->pluck('id');

        if ($oldAuRates->isNotEmpty()) {
            Price::query()
                ->where('priceable_type', (new ShippingRate)->getMorphClass())
                ->whereIn('priceable_id', $oldAuRates)
                ->delete();

            ShippingRate::query()->whereIn('id', $oldAuRates)->delete();
        }

        $auShippingRate = ShippingRate::query()->updateOrCreate([
            'shipping_zone_id' => $auShippingZone->id,
            'shipping_method_id' => $auShipping->id,
        ], [
            'enabled' => true,
        ]);

        $auShippingZone->countries()->sync(
            Country::where('iso3', '=', 'AUS')->first()->id,
        );

        Price::query()
            ->where('priceable_type', (new ShippingRate)->getMorphClass())
            ->where('priceable_id', $auShippingRate->id)
            ->delete();

        Price::create([
            'priceable_type' => (new ShippingRate)->getMorphClass(),
            'priceable_id' => $auShippingRate->id,
            'price' => 1500,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
        ]);
    }
}
