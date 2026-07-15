<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default WhatsApp Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "fonnte", "wablas", "woowa"
    | The system uses an abstraction layer so providers can be swapped
    | by changing this value without modifying business logic.
    |
    */
    'provider' => env('WHATSAPP_PROVIDER', 'fonnte'),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable WhatsApp Notifications
    |--------------------------------------------------------------------------
    */
    'enabled' => env('WHATSAPP_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_per_minute' => env('WHATSAPP_RATE_LIMIT', 30),
        'retry_attempts' => 3,
        'retry_delay_seconds' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'fonnte' => [
            'api_url' => env('WHATSAPP_API_URL', 'https://api.fonnte.com/send'),
            'api_token' => env('WHATSAPP_API_TOKEN', ''),
            'device' => env('WHATSAPP_SENDER_DEVICE', ''),
        ],

        'wablas' => [
            'api_url' => env('WHATSAPP_API_URL', 'https://pati.wablas.com/api/send-message'),
            'api_token' => env('WHATSAPP_API_TOKEN', ''),
        ],

        'woowa' => [
            'api_url' => env('WHATSAPP_API_URL', 'https://app.woowa.id/api/sendmessage'),
            'api_token' => env('WHATSAPP_API_TOKEN', ''),
        ],

    ],

];
