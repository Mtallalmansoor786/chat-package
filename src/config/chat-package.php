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

    /*
    |--------------------------------------------------------------------------
    | Popup Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable the popup chat feature.
    | Set to true to enable the popup chat component.
    |
    */
    'popup_enabled' => env('CHAT_POPUP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Blade UI Configuration
    |--------------------------------------------------------------------------
    |
    | This setting controls whether Blade views are published/available.
    | It does NOT enable web routes - this package is 100% API-based.
    |
    | Set to true if you want to use the package's Blade views in your
    | own routes/controllers. The views will use JavaScript to call the
    | API endpoints at /api/chat/*.
    |
    | Set to false if you're building a completely custom frontend
    | (React, Vue, Angular, etc.) and don't need the Blade views.
    |
    | NOTE: Web routes are completely removed. All functionality is
    | available via API endpoints at /api/chat/* regardless of this setting.
    |
    */
    'ui_enabled' => env('CHAT_UI_ENABLED', false),
];

