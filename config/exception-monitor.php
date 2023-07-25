<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Enabled sender drivers
     |--------------------------------------------------------------------------
     |
     | Send a notification about exception in your application to supported channels.
     |
     | Supported: "mail", "slack". You can use multiple drivers.
     |
     */
    'drivers' => ['mail'],

    /*
     |--------------------------------------------------------------------------
     | Enabled application environments
     |--------------------------------------------------------------------------
     |
     | Set environments that should generate notifications.
     |
     */
    'environments' => ['production'],

    /*
     |--------------------------------------------------------------------------
     | Disable Error Notifications
     |--------------------------------------------------------------------------
     |
     | Set status code for disable notifications. like [401,404,500] 
     |
     */
    'skip_error' => [401, 404, 405, 500],

    /*
     |--------------------------------------------------------------------------
     | Mail Configuration
     |--------------------------------------------------------------------------
     |
     | It uses your app default Mail driver. You shouldn't probably touch the view
     | property unless you know what you're doing.
     |
     */
    'mail' => [
        'from' => 'sender@example.com',
        'to' => 'recipient@example.com',
        'view' => 'exception-monitor::email'
    ],

    /*
     * Uses maknz\slack package.
     */
    'slack' => [
        'channel' => '#bugtracker',
        'username' => 'Exception Monitor',
        'icon' => ':robot_face:',
    ],
];
