<?php

namespace Loctracker\ActivityLogger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Loctracker\ActivityLogger\Traits\ActivityLogReporting;

class ActivityLog extends Model
{
    use ActivityLogReporting;

    protected $table = 'activity_logs';

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
        'properties' => 'array'
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
            'Causer ID/IP',
            'Properties'
        ];

        $file = fopen($filePath, 'w');
        fputcsv($file, $headers);

        $query->chunk(1000, function ($activities) use ($file) {
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at,
                    $activity->action,
                    $activity->description,
                    $activity->subject_type,
                    $activity->subject_id,
                    $activity->causer_type,
                    $activity->causer_id ?? ($activity->properties['ip_address'] ?? 'N/A'),
                    json_encode($activity->properties)
                ]);
            }
        });

        fclose($file);
    }
}
