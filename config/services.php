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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Live shipping rates at checkout (Roadmap Phase 18). Off by default —
    // flip SHIPPO_ENABLED once a real API token is set, so this ships to
    // main safely and can be disabled again with one env var if the client
    // doesn't want it.
    'shippo' => [
        'enabled' => env('SHIPPO_ENABLED', false),
        'token' => env('SHIPPO_TOKEN'),
        'from_address' => [
            'name' => env('SHIPPO_FROM_NAME', 'Bulkare Fulfillment'),
            'street1' => env('SHIPPO_FROM_STREET1', '417 Montgomery St'),
            'city' => env('SHIPPO_FROM_CITY', 'San Francisco'),
            'state' => env('SHIPPO_FROM_STATE', 'CA'),
            'zip' => env('SHIPPO_FROM_ZIP', '94104'),
            'country' => env('SHIPPO_FROM_COUNTRY', 'US'),
        ],
    ],

];
