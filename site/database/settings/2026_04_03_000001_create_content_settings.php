<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateContentSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('content.footer_text', 'Premium Pet deals for life.');
        $this->migrator->add('content.banner_text', null);
        $this->migrator->add('content.contact_email', '');
        $this->migrator->add('content.terms_conditions', '');
        $this->migrator->add('content.privacy_policy', '');
        $this->migrator->add('content.return_policy', '');
        $this->migrator->add('content.shipping_policy', '');
    }
}
