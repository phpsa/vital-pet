<?php

return [
    'webhook_path' => env('AIRWALLEX_WEBHOOK_PATH', 'airwallex/webhook'),

    'api_base' => env('AIRWALLEX_API_BASE', 'https://api.airwallex.com'),

    'js_url' => env('AIRWALLEX_JS_URL', 'https://static.airwallex.com/components/sdk/v1/index.js'),

    'env' => env('AIRWALLEX_ENV', 'demo'),

    // Supported: embedded, redirect
    'mode' => env('AIRWALLEX_MODE', 'embedded'),

    // Enable detailed (redacted) API and authorization decision logs.
    'log_api_responses' => (bool) env('AIRWALLEX_LOG_API_RESPONSES', true),

    'authorized_status' => env('AIRWALLEX_AUTHORIZED_ORDER_STATUS', 'payment-received'),

    'final_statuses' => [
        'SUCCEEDED',
        'FAILED',
        'CANCELLED',
    ],

    'success_statuses' => [
        'SUCCEEDED',
    ],

    'status_mapping' => [
        'SUCCEEDED' => 'payment-received',
        'REQUIRES_PAYMENT_METHOD' => 'awaiting-payment',
        'REQUIRES_CUSTOMER_ACTION' => 'awaiting-payment',
        'PENDING' => 'processing',
        'FAILED' => 'failed',
        'CANCELLED' => 'cancelled',
    ],

    'intent' => [
        'create_endpoint' => '/api/v1/pa/payment_intents/create',
        'retrieve_endpoint' => '/api/v1/pa/payment_intents/{intent_id}',
    ],
];
