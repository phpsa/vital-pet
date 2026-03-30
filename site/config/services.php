<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'public_key' => env('STRIPE_KEY'),
        'key' => env('STRIPE_SECRET'),
    ],

    'airwallex' => [
        'client_id' => env('AIRWALLEX_CLIENT_ID'),
        'api_key' => env('AIRWALLEX_API_KEY'),
    ],

    'landing' => [
        'signing_key' => env('LANDING_SIGNING_KEY'),
        'gateway_url' => env('LANDING_GATEWAY_URL'),
    ],

    'sending' => [
        'signing_key' => env('SENDING_SIGNING_KEY'),
        'landing_url' => env('SENDING_LANDING_URL'),
    ],

    'store' => [
        'admin_email' => env('STORE_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
    ],

];
