<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pusher Configuration
    |--------------------------------------------------------------------------
    |
    | These values will be automatically fetched from the parent project's .env
    | file. Make sure PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET,
    | PUSHER_APP_CLUSTER are set in your .env file.
    |
    */
    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
        'useTLS' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the chat package.
    |
    */
    'chat' => [
        'per_page' => 50,
        'max_message_length' => 1000,
    ],
];

