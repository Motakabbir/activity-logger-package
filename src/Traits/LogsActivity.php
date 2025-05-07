<?php

namespace Loctracker\ActivityLogger\Traits;

use Illuminate\Database\Eloquent\Model;
use Loctracker\ActivityLogger\Facades\ActivityLogger;

trait LogsActivity
{
    /**
     * Get the attributes that should be logged
     */
    public static function getLogAttributes(): ?array
    {
        return static::$logAttributes ?? config('activity-logger.log_attributes', []);
    }

    /**
     * Get the attributes that should be excluded from logging
     */
    public static function getLogExcept(): ?array
    {
        return static::$logExcept ?? config('activity-logger.log_except', []);
    }

    protected static function bootLogsActivity()
    {
        $events = config('activity-logger.log_events', []);

        if ($events['created'] ?? true) {
            static::created(function (Model $model) {
                static::logActivity('created', $model);
            });
        }

        if ($events['updated'] ?? true) {
            static::updated(function (Model $model) {
                $changes = $model->getDirty();
                if (!empty($changes)) {
                    static::logActivity('updated', $model, [
                        'old' => array_intersect_key($model->getOriginal(), $changes),
                        'attributes' => $changes
                    ]);
                }
            });
        }

        if ($events['deleted'] ?? true) {
            static::deleted(function (Model $model) {
                static::logActivity('deleted', $model);
            });
        }

        if (($events['restored'] ?? true) && method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                static::logActivity('restored', $model);
            });
        }
    }

    protected static function logActivity(string $action, Model $model, array $properties = [])
    {
        // Get the default properties if configured
        $defaultProps = config('activity-logger.default_log_properties', []);
        if ($defaultProps['ip_address'] ?? false) {
            $properties['ip_address'] = request()->ip();
        }
        if ($defaultProps['user_agent'] ?? false) {
            $properties['user_agent'] = request()->userAgent();
        }
        if ($defaultProps['request_method'] ?? false) {
            $properties['request_method'] = request()->method();
        }
        if ($defaultProps['request_url'] ?? false) {
            $properties['request_url'] = request()->fullUrl();
        }

        // Handle attribute logging
        $logAttributes = static::getLogAttributes();
        $logExcept = static::getLogExcept();
        $attributes = $model->getAttributes();

        if (!empty($logAttributes)) {
            // Log only specified attributes
            $properties['logged_attributes'] = array_intersect_key(
                $attributes,
                array_flip($logAttributes)
            );
        } elseif (!empty($logExcept)) {
            // Log all attributes except specified ones
            $properties['logged_attributes'] = array_diff_key(
                $attributes,
                array_flip($logExcept)
            );
        } else {
            // Log all attributes
            $properties['logged_attributes'] = $attributes;
        }

        // Filter out any sensitive data defined in config
        $sensitiveFields = config('activity-logger.sensitive_fields', ['password', 'remember_token']);
        foreach ($sensitiveFields as $field) {
            if (isset($properties['logged_attributes'][$field])) {
                $properties['logged_attributes'][$field] = '******';
            }
        }

        $description = sprintf(
            '%s %s %s',
            class_basename($model),
            $action,
            $model->getKey()
        );

        // Get the causer (authenticated user)
        $causer = auth()->user();

        $logger = app('activitylogger');

        if ($causer) {
            $logger->causedBy($causer);
        }

        if (config('activity-logger.queue.enabled')) {
            $logger->withQueue();
        }

        $logger->withProperties($properties)
            ->log($action, $description, $model);
    }

    /**
     * Get all activity logs for the model
     */
    public function activities()
    {
        return $this->morphMany(
            config('activity-logger.activity_model'),
            'subject'
        );
    }
}
