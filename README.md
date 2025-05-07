# Activity Logger Package

A powerful and easy-to-use Laravel package for automatically tracking model changes and user activities within your application. This package provides automatic model tracking and manual activity logging capabilities.

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

## Migration

The package includes a migration file to create the `activity_logs` table. You can run the migration using:

```bash
php artisan migrate
```

## Testing

To run the tests for the Activity Logger Package, use PHPUnit:

```bash
vendor/bin/phpunit
```

## License

This package is open-source and available under the MIT License. See the [LICENSE](LICENSE) file for more information.

## Contributing

If you would like to contribute to the Activity Logger Package, please fork the repository and submit a pull request. Your contributions are welcome!