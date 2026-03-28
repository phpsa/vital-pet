<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Country;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        $taxClass = TaxClass::first();

        $nzCountry = Country::firstWhere('iso3', 'NZL');
        $auCountry = Country::firstWhere('iso3', 'AUS');
        $defaultZone = TaxZone::query()->firstWhere('name', 'Default Tax Zone');

        TaxZone::query()->update(['default' => false]);

        if ($defaultZone) {
            TaxZoneCountry::query()
                ->where('tax_zone_id', $defaultZone->id)
                ->whereIn('country_id', array_filter([$nzCountry?->id, $auCountry?->id]))
                ->delete();
        }

        $nzTaxZone = TaxZone::query()->updateOrCreate(
            ['name' => 'New Zealand'],
            [
                'active' => true,
                'default' => false,
                'zone_type' => 'country',
                'price_display' => 'tax_exclusive',
            ]
        );

        TaxZoneCountry::query()->firstOrCreate([
            'country_id' => $nzCountry->id,
            'tax_zone_id' => $nzTaxZone->id,
        ]);

        $auTaxZone = TaxZone::query()->updateOrCreate(
            ['name' => 'Australia'],
            [
                'active' => true,
                'default' => true,
                'zone_type' => 'country',
                'price_display' => 'tax_exclusive',
            ]
        );

        TaxZoneCountry::query()->firstOrCreate([
            'country_id' => $auCountry->id,
            'tax_zone_id' => $auTaxZone->id,
        ]);

        $nzRate = TaxRate::query()->updateOrCreate(
            [
                'tax_zone_id' => $nzTaxZone->id,
                'name' => 'GST',
            ],
            [
                'priority' => 1,
            ]
        );

        $auRate = TaxRate::query()->updateOrCreate(
            [
                'tax_zone_id' => $auTaxZone->id,
                'name' => 'GST',
            ],
            [
                'priority' => 1,
            ]
        );

        TaxRateAmount::query()->where('tax_rate_id', $nzRate->id)->delete();
        TaxRateAmount::query()->where('tax_rate_id', $auRate->id)->delete();

        $nzRate->taxRateAmounts()->create([
            'percentage' => 15,
            'tax_class_id' => $taxClass->id,
        ]);

        $auRate->taxRateAmounts()->create([
            'percentage' => 10,
            'tax_class_id' => $taxClass->id,
        ]);
    }
}
