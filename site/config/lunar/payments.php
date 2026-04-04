<?php

return [

    'default' => env('PAYMENTS_TYPE', 'airwallex'),

    'types' => [
        'airwallex' => [
            'driver' => 'airwallex',
        ],
        'paypal' => [
            'driver' => 'paypal',
        ],
    ],

    'paypal' => [
        'success_route' => 'paypal.return',
        'cancel_route' => 'checkout.view',
    ],

];
