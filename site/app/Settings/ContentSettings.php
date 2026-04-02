<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContentSettings extends Settings
{
    public ?string $logo_path = null;

    public ?string $site_title = null;

    public ?string $meta_description = null;

    public ?string $meta_keywords = null;

    public ?string $footer_text = '';

    public ?string $banner_text = null;

    public ?string $contact_email = '';

    public ?string $terms_conditions = '';

    public ?string $privacy_policy = '';

    public ?string $return_policy = '';

    public ?string $shipping_policy = '';

    public static function group(): string
    {
        return 'content';
    }
}
