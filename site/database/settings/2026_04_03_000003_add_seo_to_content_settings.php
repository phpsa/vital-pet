<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddSeoToContentSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('content.site_title', null);
        $this->migrator->add('content.meta_description', null);
        $this->migrator->add('content.meta_keywords', null);
    }
}
