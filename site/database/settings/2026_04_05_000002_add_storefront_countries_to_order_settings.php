<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddStorefrontCountriesToOrderSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('order.storefront_country_iso2', ['AU', 'NZ']);
    }
}
