<?php

return [

    'default' => env('PAYMENTS_TYPE', 'airwallex'),

    'types' => [
        'airwallex' => [
            'driver' => 'airwallex',
        ],
    ],

];
