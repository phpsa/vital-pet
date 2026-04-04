<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateOrderSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('order.min_order_value', 0);
    }
}
