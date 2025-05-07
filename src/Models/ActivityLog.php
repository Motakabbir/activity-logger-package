<?php

namespace Loctracker\ActivityLogger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Loctracker\ActivityLogger\Traits\ActivityLogReporting;

class ActivityLog extends Model
{
    use ActivityLogReporting;

    /**
     * Get the table name from config
     */
    public function getTable()
    {
        return config('activity-logger.table_name', 'activity_logs');
    }

    protected $fillable = [
        'action',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties'
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the subject of the activity
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Format dates according to config
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format(config('activity-logger.date_format', 'Y-m-d H:i:s'));
    }

    /**
     * Override toArray to limit properties size
     */
    public function toArray()
    {
        $array = parent::toArray();

        $maxSize = config('activity-logger.properties_max_size', 100000);

        if (isset($array['properties']) && strlen(json_encode($array['properties'])) > $maxSize) {
            $array['properties'] = [
                'error' => 'Properties exceeded maximum size',
                'size' => strlen(json_encode($array['properties']))
            ];
        }

        return $array;
    }

    /**
     * Export activities to CSV
     */
    public function exportToCsv($query, $filePath)
    {
        $headers = [
            'Date',
            'Action',
            'Description',
            'Subject Type',
            'Subject ID',
            'Causer Type',
            'Causer ID',
            'Properties'
        ];

        $handle = fopen($filePath, 'w');
        fputcsv($handle, $headers);

        $query->chunk(1000, function ($activities) use ($handle) {
            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->created_at,
                    $activity->action,
                    $activity->description,
                    $activity->subject_type,
                    $activity->subject_id,
                    $activity->causer_type,
                    $activity->causer_id,
                    json_encode($activity->properties)
                ]);
            }
        });

        fclose($handle);
    }
}
