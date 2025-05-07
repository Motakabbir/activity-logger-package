<?php

namespace Loctracker\ActivityLogger;

use Loctracker\ActivityLogger\Contracts\ActivityLoggerInterface;
use Loctracker\ActivityLogger\Models\ActivityLog;

class ActivityLogger implements ActivityLoggerInterface
{
    protected $properties = [];
    protected $causer = null;

    /**
     * Log an activity
     *
     * @param string $action
     * @param string $description
     * @param mixed $subject
     * @param array $properties
     * @return ActivityLog
     */
    public function log(string $action, string $description, $subject = null, array $properties = [])
    {
        $properties = array_merge($this->properties, $properties);

        $activity = new ActivityLog([
            'action' => $action,
            'description' => $description,
            'properties' => $properties
        ]);

        if ($subject) {
            $activity->subject_type = get_class($subject);
            $activity->subject_id = $subject->getKey();
        }

        if ($this->causer) {
            $activity->causer_type = get_class($this->causer);
            $activity->causer_id = $this->causer->getKey();
        }

        $activity->save();

        // Reset the properties and causer
        $this->properties = [];
        $this->causer = null;

        return $activity;
    }

    /**
     * Set custom fields for the activity log
     *
     * @param array $fields
     * @return self
     */
    public function withProperties(array $fields)
    {
        $this->properties = array_merge($this->properties, $fields);
        return $this;
    }

    /**
     * Set the causer of the activity
     *
     * @param mixed $causer
     * @return self
     */
    public function causedBy($causer)
    {
        $this->causer = $causer;
        return $this;
    }

    /**
     * Get activity logs for a specific subject
     *
     * @param mixed $subject
     * @return mixed
     */
    public function getActivityFor($subject)
    {
        return ActivityLog::forSubject($subject)->get();
    }
}
