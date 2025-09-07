<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LogStation Enabled
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable LogStation. By default, LogStation
    | is enabled in all environments except production. You may override
    | this behavior by setting this value.
    |
    */

    'enabled' => env('LOGSTATION_ENABLED', null),

    /*
    |--------------------------------------------------------------------------
    | LogStation Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where LogStation will be accessible from. Feel free
    | to change this path to anything you like.
    |
    */

    'path' => env('LOGSTATION_PATH', 'logstation'),

    /*
    |--------------------------------------------------------------------------
    | LogStation Logo
    |--------------------------------------------------------------------------
    |
    | This option allows you to specify a custom logo URL for the LogStation
    | dashboard. If set to null, the default LogStation logo will be used.
    |
    */

    'logo_url' => env('LOGSTATION_LOGO_URL', null),

    /*
    |--------------------------------------------------------------------------
    | LogStation Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the storage driver that will
    | be used to store LogStation's data. Supported: "database", "file"
    |
    */

    'driver' => env('LOGSTATION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | LogStation Database Connection
    |--------------------------------------------------------------------------
    |
    | This database connection will be used to store LogStation's data.
    | You may use a separate database connection to avoid performance
    | impact on your main application database.
    |
    */

    'database' => [
        'connection' => env('LOGSTATION_DB_CONNECTION', null),
        'chunk' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | LogStation File Storage
    |--------------------------------------------------------------------------
    |
    | When using the file driver, LogStation will store logs in the
    | specified directory. Files will be rotated based on the max_files
    | setting to prevent unlimited growth.
    |
    */

    'storage' => [
        'path' => env('LOGSTATION_STORAGE_PATH', 'logstation'),
        'max_files' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | This option controls how long LogStation will retain log entries
    | before they are pruned. Set to null to disable automatic pruning.
    |
    */

    'retention' => [
        'days' => env('LOGSTATION_RETENTION_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | LogStation Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every LogStation route, giving
    | you the chance to add your own middleware to this list or change
    | any of the existing middleware.
    |
    */

    'middleware' => [
        'web',
        PlusinfoLab\Logstation\Http\Middleware\Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Paths
    |--------------------------------------------------------------------------
    |
    | The following list of URI paths will not be watched by LogStation.
    | This is useful for preventing LogStation from recording its own
    | requests and other paths that should not be logged.
    |
    */

    'ignore_paths' => [
        'logstation*',
        'horizon*',
        'telescope*',
        'nova-api*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Commands
    |--------------------------------------------------------------------------
    |
    | The following list of Artisan commands will not be watched by
    | LogStation. This is useful for preventing LogStation from
    | recording its own commands.
    |
    */

    'ignore_commands' => [
        'logstation:*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Watchers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "watchers" that will be registered with
    | LogStation. The watchers gather the application's log data when
    | a log entry is recorded.
    |
    */

    'watchers' => [
        PlusinfoLab\Logstation\Watchers\LogWatcher::class => [
            'enabled' => env('LOGSTATION_LOG_WATCHER', true),
            'channels' => ['stack', 'single', 'daily'],
            'levels' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
        ],

        PlusinfoLab\Logstation\Watchers\ExceptionWatcher::class => [
            'enabled' => env('LOGSTATION_EXCEPTION_WATCHER', true),
            'ignore' => [
                // Add exception classes to ignore
            ],
        ],

        PlusinfoLab\Logstation\Watchers\QueryWatcher::class => [
            'enabled' => env('LOGSTATION_QUERY_WATCHER', false),
            'slow' => 100, // Log queries slower than this (ms)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-time Broadcasting
    |--------------------------------------------------------------------------
    |
    | LogStation can broadcast new log entries in real-time using Laravel's
    | broadcasting system. Configure your broadcasting driver and enable
    | this feature to see logs appear instantly in the UI.
    |
    */

    'broadcasting' => [
        'enabled' => env('LOGSTATION_BROADCASTING', false),
        'channel' => 'logstation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Formats
    |--------------------------------------------------------------------------
    |
    | The following array lists the export formats that are available
    | in LogStation. You can disable formats you don't need.
    |
    */

    'export' => [
        'formats' => ['json', 'csv', 'txt'],
        'max_entries' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tags
    |--------------------------------------------------------------------------
    |
    | LogStation can automatically tag log entries with useful information
    | like user ID, request ID, and more. Configure which tags should
    | be automatically applied.
    |
    */

    'tags' => [
        'user_id' => true,
        'request_id' => true,
        'session_id' => false,
        'ip_address' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | LogStation uses a "viewLogstation" gate to determine who can access
    | the LogStation dashboard. You can customize this authorization logic
    | by publishing the config file and modifying the callback below.
    |
    | To publish the config: php artisan vendor:publish --tag=logstation-config
    |
    | Examples:
    | - Allow only specific users: fn($request) => in_array($request->user()?->email, ['admin@example.com'])
    | - Use a Gate: fn($request) => Gate::allows('viewLogstation', $request->user())
    | - Use a Policy: fn($request) => $request->user()?->can('viewLogstation')
    | - Check user role: fn($request) => $request->user()?->hasRole('admin')
    | - Environment based: fn($request) => app()->environment(['local', 'staging'])
    |
    */

    'authorization' => [
        'callback' => function ($request) {
            // Default: Allow access in local environment only
            // Customize this after publishing the config file
            return app()->environment('local');
        },
    ],
];
