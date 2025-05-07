# Activity Logger Package

A powerful and easy-to-use Laravel package for automatically tracking model changes and user activities within your application. This package provides automatic model tracking and manual activity logging capabilities.

## Requirements

- PHP 7.4 or higher
- Laravel 8.0 or higher
- MySQL 5.7+ or PostgreSQL 9.6+

## Installation

To install the Activity Logger Package, you can use Composer:

```bash
composer require loctracker/activity-logger
```

## Configuration

After installation, publish the configuration and migrations:

```bash
php artisan vendor:publish --provider="Loctracker\ActivityLogger\ActivityLoggerServiceProvider"
```

Then run the migrations:

```bash
php artisan migrate
```

### Configuration Options

The package can be configured through the `config/activity-logger.php` file. Here are all available options:

```php
return [
    // The logging channel to use for activity logs
    'log_channel' => env('ACTIVITY_LOG_CHANNEL', 'stack'),

    // Log level for activities
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

    // Model events to log
    'log_events' => [
        'created' => true,
        'updated' => true,
        'deleted' => true,
        'restored' => true,
    ],

    // Default properties to log for each activity
    'default_log_properties' => [
        'ip_address' => true,    // Log IP address
        'user_agent' => true,    // Log browser user agent
        'request_method' => true, // Log HTTP method
        'request_url' => true,    // Log full URL
    ],

    // Maximum size for the properties JSON column
    'properties_max_size' => 100000,

    // Enable automatic logging of authentication events
    'log_auth_events' => true,

    // Cleanup configuration for old records
    'cleanup' => [
        'enabled' => false,
        'keep_logs_for_days' => 365,
        'chunk_size' => 1000,
    ],

    // Date format for activity timestamps
    'date_format' => 'Y-m-d H:i:s',

    // Queue configuration for logging activities
    'queue' => [
        'enabled' => false,
        'connection' => env('QUEUE_CONNECTION', 'sync'),
        'queue' => 'activity-logs',
    ],

    // Default attributes to log for all models
    'log_attributes' => ['title', 'content', 'status'],

    // Default attributes to exclude from logging
    'log_except' => ['password', 'remember_token'],

    // Fields that should always be masked in the log
    'sensitive_fields' => ['password', 'credit_card', 'api_key'],
];
```

## Usage

### Automatic Model Activity Logging

Simply add the `LogsActivity` trait to any model you want to track:

```php
use Loctracker\ActivityLogger\Traits\LogsActivity;

class Post extends Model
{
    use LogsActivity;

    // Optional: specify which attributes to log
    protected static $logAttributes = ['title', 'content', 'status'];

    // OR specify which attributes to exclude from logging
    protected static $logExcept = ['password', 'remember_token'];
}
```

Now all create/update/delete operations on this model will be automatically logged!

### Configuring Attribute Logging

You can configure which attributes to log in three ways:

1. In your model using properties:
```php
use Loctracker\ActivityLogger\Traits\LogsActivity;

class Post extends Model
{
    use LogsActivity;

    // Option 1: Specify which attributes to log
    protected static $logAttributes = ['title', 'content', 'status'];

    // Option 2: Specify which attributes to exclude from logging
    protected static $logExcept = ['password', 'remember_token'];
}
```

2. In the configuration file for all models:
```php
// config/activity-logger.php

return [
    // Default attributes to log for all models
    'log_attributes' => ['title', 'content', 'status'],

    // Default attributes to exclude from logging
    'log_except' => ['password', 'remember_token'],

    // Fields that should always be masked in the log
    'sensitive_fields' => ['password', 'credit_card', 'api_key'],
];
```

3. Let the package log all attributes (default behavior if no configuration is provided)

The priority order is:
1. Model-specific configuration (`$logAttributes` or `$logExcept` in the model)
2. Global configuration in `config/activity-logger.php`
3. Log all attributes if no configuration is found

Sensitive fields defined in `sensitive_fields` will always be masked with '******' in the logs, regardless of how attributes are configured.

### Retrieving Logs for a Model

```php
// Get all activities for a specific post
$post = Post::find(1);
$activities = $post->activities;

// Each activity contains:
foreach ($activities as $activity) {
    echo $activity->action;        // 'created', 'updated', 'deleted'
    echo $activity->description;   // 'Post created 1', 'Post updated 1'
    echo $activity->causer;        // User who performed the action
    echo $activity->properties;    // Changed attributes and their values
}
```

### Manual Logging

You can also log activities manually using the facade:

```php
use Loctracker\ActivityLogger\Facades\ActivityLogger;

// Simple log
ActivityLogger::log(
    'custom-action',
    'Custom activity description',
    $model // optional
);

// Log with additional properties
ActivityLogger::withProperties(['key' => 'value'])
    ->log('custom-action', 'Custom activity with properties', $model);

// Log with specific causer
ActivityLogger::causedBy($user)
    ->log('custom-action', 'Activity by specific user', $model);
```

### Cleanup Old Records

To clean up old activity logs, you can use the provided artisan command:

```bash
php artisan activitylog:clean
```

This command will delete records older than the configured retention period (`keep_logs_for_days`).

## Customization

### Custom Activity Model

You can use your own activity model by specifying it in the configuration:

```php
'activity_model' => \App\Models\CustomActivityLog::class
```

Your custom model should extend the base `ActivityLog` model or implement the same interface.

### Queue Configuration

To improve performance, you can enable queued logging:

```php
'queue' => [
    'enabled' => true,
    'connection' => 'redis',
    'queue' => 'activity-logs',
]
```

This will process the activity logging in the background.

## Testing

To run the tests for the Activity Logger Package, use PHPUnit:

```bash
vendor/bin/phpunit
```

## License

This package is open-source and available under the MIT License. See the [LICENSE](LICENSE) file for more information.

## Contributing

Contributions are welcome! If you would like to contribute:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and follow the existing coding style.