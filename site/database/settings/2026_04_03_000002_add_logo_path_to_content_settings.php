<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddLogoPathToContentSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('content.logo_path', null);
    }
}
