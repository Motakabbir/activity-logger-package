<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Logger Settings
    |--------------------------------------------------------------------------
    |
    | Here you can configure the behavior of the activity logger package.
    |
    */

    // The logging channel to use for activity logs
    'log_channel' => env('ACTIVITY_LOG_CHANNEL', 'stack'),

    // Log level for activities (debug, info, notice, warning, error, critical, alert, emergency)
    'log_level' => env('ACTIVITY_LOG_LEVEL', 'info'),

    // The model used for storing activity logs
    'activity_model' => \Loctracker\ActivityLogger\Models\ActivityLog::class,

    // Database table name for activity logs
    'table_name' => 'activity_logs',

    // Routes that should not be logged
    'ignore_routes' => [
        'horizon*',
        'nova*',
        '_debugbar*',
        'sanctum*',
        'telescope*',
    ],

    // Model events to log (created, updated, deleted, restored)
    'log_events' => [
        'created' => true,
        'updated' => true,
        'deleted' => true,
        'restored' => true,
    ],

    // Default properties to log for each activity
    'default_log_properties' => [
        'ip_address' => true,
        'user_agent' => true,
        'request_method' => true,
        'request_url' => true,
    ],

    // Maximum size for the properties JSON column (in characters)
    'properties_max_size' => 100000,

    // Enable automatic logging of authentication events
    'log_auth_events' => true,

    // Enable automatic cleanup of old records
    'cleanup' => [
        'enabled' => false,
        'keep_logs_for_days' => 365,
        'chunk_size' => 1000,
    ],

    // Define custom date format for activity timestamps
    'date_format' => 'Y-m-d H:i:s',

    // Queue configuration for logging activities
    'queue' => [
        'enabled' => false,
        'connection' => env('QUEUE_CONNECTION', 'sync'),
        'queue' => 'activity-logs',
    ],
];
