<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lenco by BroadPay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Lenco by BroadPay payment gateway.
    | Handles Mobile Money collections (Airtel, MTN, Zamtel) and Card payments.
    |
    */

    'base_url' => env('BROADPAY_BASE_URL', env('LENCO_BASE_URL', 'https://api.broadpay.io/v1')),

    'public_key' => env('BROADPAY_PUBLIC_KEY', env('LENCO_PUBLIC_KEY', '')),

    'secret_key' => env('BROADPAY_SECRET_KEY', env('LENCO_SECRET_KEY', '')),

    'webhook_secret' => env('BROADPAY_WEBHOOK_SECRET', env('LENCO_WEBHOOK_SECRET', '')),

    'account_id' => env('BROADPAY_ACCOUNT_ID', env('LENCO_ACCOUNT_ID', '')),

    'currency' => env('BROADPAY_CURRENCY', 'ZMW'),

    'environment' => env('BROADPAY_ENVIRONMENT', env('APP_ENV', 'production')),

    'timeout' => (int) env('BROADPAY_TIMEOUT', 30),

];
