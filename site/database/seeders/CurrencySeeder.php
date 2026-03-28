<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Ensure AUD is present and set as the default currency.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Currency::query()->where('code', '!=', 'AUD')->delete();
            Currency::query()->update(['default' => false]);

            Currency::updateOrCreate(
                ['code' => 'AUD'],
                [
                    'name' => 'Australian Dollar',
                    'exchange_rate' => 1,
                    'decimal_places' => 2,
                    'enabled' => true,
                    'default' => true,
                ]
            );
        });
    }
}
