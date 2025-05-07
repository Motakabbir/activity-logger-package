<?php

return [
    'log_channel' => env('ACTIVITY_LOG_CHANNEL', 'stack'),
    'log_level' => env('ACTIVITY_LOG_LEVEL', 'info'),
    'log_model' => \App\Models\ActivityLog::class,
    'ignore_routes' => [
        // Add routes to ignore logging here
    ],
];