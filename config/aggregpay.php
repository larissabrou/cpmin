<?php

return [

    'brand' => env('AGGREGPAY_BRAND', 'AggregPay'),

    'colors' => [
        'primary' => '#10149a',
        'secondary' => '#f89f4d',
    ],

    'gateway_url' => env('AGGREGPAY_GATEWAY_URL', 'http://127.0.0.1:8080'),

    'docs_path' => env(
        'AGGREGPAY_DOCS_PATH',
        base_path('../payment-gateway/docs/PARTNER_API.md'),
    ),

];
