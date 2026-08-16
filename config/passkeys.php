<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Relying Party ID
    |--------------------------------------------------------------------------
    |
    | The relying party ID represents your application in the WebAuthn protocol.
    | This is typically your domain (e.g., "example.com"). Passkeys are bound
    | to this ID and can only be verified on matching domains.
    |
    */

    'relying_party_id' => env('PASSKEYS_RP_ID') ?: (
        isset($_SERVER['HTTP_HOST'])
            ? preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])
            : (parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost')
    ),

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | The origins permitted to complete WebAuthn ceremonies. Passkeys bound
    | to the relying party ID above will only verify when the browser
    | reports one of these origins. Defaults to your application URL.
    |
    */

    'allowed_origins' => array_values(array_unique(array_filter([
        config('app.url'),
        env('APP_URL'),
        'https://thinkerhub.online',
        'http://thinkerhub.online',
        'https://www.thinkerhub.online',
        'http://www.thinkerhub.online',
        'http://localhost:8000',
        'http://localhost',
        'http://127.0.0.1:8000',
        'http://127.0.0.1',
        isset($_SERVER['HTTP_HOST']) ? ('https://' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])) : null,
        isset($_SERVER['HTTP_HOST']) ? ('http://' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'])) : null,
        isset($_SERVER['HTTP_HOST']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']) : null,
    ]))),

    /*
    |--------------------------------------------------------------------------
    | User Handle Secret
    |--------------------------------------------------------------------------
    |
    | Secret used to derive a stable WebAuthn user handle from each user model.
    | Set this explicitly if you rotate your application key.
    |
    */

    'user_handle_secret' => env('PASSKEYS_USER_HANDLE_SECRET', config('app.key')),

    /*
    |--------------------------------------------------------------------------
    | WebAuthn Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in milliseconds for WebAuthn operations. This determines
    | how long users have to complete passkey registration or verification.
    |
    */

    'timeout' => 60000,

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard to use when logging in users with passkeys.
    | This should match your application's primary authentication guard.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Passkeys Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Passkeys will assign to the routes
    | that it registers with the application. If necessary, you may change
    | these middleware but typically this provided default is preferred.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Passkeys Management Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify the middleware applied to passkey management routes
    | that create or delete passkeys.
    |
    */

    'management_middleware' => ['auth'],

    /*
    |--------------------------------------------------------------------------
    | Passkeys Throttling
    |--------------------------------------------------------------------------
    |
    | Middleware used to throttle passkey endpoints. Set to null to disable.
    |
    */

    'throttle' => 'throttle:60,1',

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    |
    | The path to redirect to after successful passkey verification.
    |
    */

    'redirect' => '/dashboard',

];
