<?php

return [

    'enabled' => env('INVERSE_LOGISTICS_ENABLED', true),

    'queue' => env('INVERSE_LOGISTICS_QUEUE', 'default'),

    'returns' => [
        'auto_approve' => false,
        'max_days_since_delivery' => 30,
    ],

    'inventory' => [
        'restock_location' => env('INVERSE_LOGISTICS_RESTOCK_LOCATION'),
    ],

    'models' => [
        'request' => 'App\\Models\\Request',
        'checkin' => 'App\\Models\\Checkin',
        'checkout' => 'App\\Models\\Checkout',
        'client' => 'App\\Models\\Client',
    ],
    'services' => [
        'checkin' => 'App\\Services\\CheckinService',
    ],

];
