<?php

namespace Loctracker\ActivityLogger\Traits;

use Illuminate\Database\Eloquent\Model;
use Loctracker\ActivityLogger\Facades\ActivityLogger;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            static::logActivity('created', $model);
        });

        static::updated(function (Model $model) {
            $changes = $model->getDirty();
            if (!empty($changes)) {
                static::logActivity('updated', $model, [
                    'old' => array_intersect_key($model->getOriginal(), $changes),
                    'attributes' => $changes
                ]);
            }
        });

        static::deleted(function (Model $model) {
            static::logActivity('deleted', $model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                static::logActivity('restored', $model);
            });
        }
    }

    protected static function logActivity(string $action, Model $model, array $properties = [])
    {
        // Get only the specified attributes if defined
        if (isset(static::$logAttributes)) {
            $properties['logged_attributes'] = array_intersect_key(
                $model->getAttributes(),
                array_flip(static::$logAttributes)
            );
        }

        // Exclude specific attributes if defined
        if (isset(static::$logExcept)) {
            $properties['logged_attributes'] = array_diff_key(
                $model->getAttributes(),
                array_flip(static::$logExcept)
            );
        }
        $description = sprintf(
            '%s %s %s',
            class_basename($model),
            $action,
            $model->getKey()
        );

        // Get the causer (authenticated user) or capture IP if no user
        $causer = auth()->user();
        if (!$causer) {
            $properties['ip_address'] = request()->ip();
            $properties['user_agent'] = request()->userAgent();
        }

        ActivityLogger::causedBy($causer)
            ->withProperties($properties)
            ->log($action, $description, $model);
    }

    /**
     * Get all activity logs for the model
     */
    public function activities()
    {
        return $this->morphMany(config('activity-logger.activity_model', \Loctracker\ActivityLogger\Models\ActivityLog::class), 'subject');
    }
}
