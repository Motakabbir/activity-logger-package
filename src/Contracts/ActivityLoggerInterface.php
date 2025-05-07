<?php

namespace Loctracker\ActivityLogger\Contracts;

interface ActivityLoggerInterface
{
    /**
     * Log an activity
     *
     * @param string $action
     * @param string $description
     * @param mixed $subject
     * @param array $properties
     * @return mixed
     */
    public function log(string $action, string $description, $subject = null, array $properties = []);

    /**
     * Set custom fields for the activity log
     *
     * @param array $fields
     * @return self
     */
    public function withProperties(array $fields);

    /**
     * Set the causer of the activity
     *
     * @param mixed $causer
     * @return self
     */
    public function causedBy($causer);

    /**
     * Get activity logs for a specific subject
     *
     * @param mixed $subject
     * @return mixed
     */
    public function getActivityFor($subject);
}
