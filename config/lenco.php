<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lenco Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Lenco payment gateway (api.lenco.co/access/v2).
    | Handles Mobile Money collections (Airtel, MTN, Zamtel) and Card collections.
    |
    */

    'api_name' => env('LENCO_API_NAME', env('BROADPAY_API_NAME', 'MUX')),

    'base_url' => env('LENCO_BASE_URL', env('BROADPAY_BASE_URL', 'https://api.lenco.co/access/v2')),

    'public_key' => env('LENCO_PUBLIC_KEY', env('BROADPAY_PUBLIC_KEY', '')),

    'secret_key' => env('LENCO_SECRET_KEY', env('BROADPAY_SECRET_KEY', '')),

    'webhook_secret' => env('LENCO_WEBHOOK_SECRET', env('BROADPAY_WEBHOOK_SECRET', '')),

    'account_id' => env('LENCO_ACCOUNT_ID', env('BROADPAY_ACCOUNT_ID', '')),

    'currency' => env('LENCO_CURRENCY', env('BROADPAY_CURRENCY', 'ZMW')),

    'environment' => env('LENCO_ENVIRONMENT', env('BROADPAY_ENVIRONMENT', env('APP_ENV', 'production'))),

    'timeout' => (int) env('LENCO_TIMEOUT', env('BROADPAY_TIMEOUT', 30)),

];
