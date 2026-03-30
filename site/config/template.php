<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Template
    |--------------------------------------------------------------------------
    |
    | Specify which template/theme to use for the frontend.
    | Available options: 'petstore', 'memberstore'
    | Views will be loaded from resources/{template}/ with fallback to common/
    |
    */

    //'active' => env('TEMPLATE', 'petstore'),
    'active' => ($_SERVER['HTTP_HOST'] ?? null) === 'store.vital.lndo.site'
        ? 'memberstore'
        : env('TEMPLATE', 'petstore'),

    /*
    |--------------------------------------------------------------------------
    | Available Templates
    |--------------------------------------------------------------------------
    |
    | List of available template options. Add new templates here and place
    | their views in resources/{template_name}/
    |
    */

    'templates' => [
        'petstore',
        'memberstore',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storefront Access Lock
    |--------------------------------------------------------------------------
    |
    | When enabled, storefront pages require an authenticated user.
    | Guests are redirected to the login page. Auth routes remain public.
    |
    */

 //   'storefront_requires_auth' => (bool) env('STOREFRONT_REQUIRES_AUTH', false),
    'storefront_requires_auth' =>($_SERVER['HTTP_HOST'] ?? null) === 'store.vital.lndo.site'
        ? true
        : (bool) env('STOREFRONT_REQUIRES_AUTH', false),

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    |
    | Configure theme-specific assets, branding, and styling for each template.
    |
    */

    'themes' => [
        'petstore' => [
            'name' => 'Pet Store',
            'description' => 'Pet-themed storefront',
            'logo' => 'img/petstore-logo.svg',
            'favicon' => 'favicon-pet.ico',
            'brand_color' => '#FF6B35',
            'image_directory' => 'img/petstore/',
            'hero_image' => 'img/petstore/hero-pets.jpg',
        ],
        'memberstore' => [
            'name' => 'Peptide Store',
            'description' => 'Scientifically-themed peptide storefront',
            'logo' => 'img/memberstore-logo.svg',
            'favicon' => 'favicon-peptide.ico',
            'brand_color' => '#0066CC',
            'image_directory' => 'img/memberstore/',
            'hero_image' => 'img/memberstore/hero-peptide.jpg',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Get Active Theme Config
    |--------------------------------------------------------------------------
    |
    | Helper to retrieve the configuration for the active template.
    | Usage: config('template.themes.' . config('template.active'))
    |
    */
];
