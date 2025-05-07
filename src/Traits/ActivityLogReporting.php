<?php

namespace Loctracker\ActivityLogger\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait ActivityLogReporting
{
    /**
     * Get activity logs within a date range
     */
    public function scopeInDateRange($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Get activity logs by subject type
     */
    public function scopeForSubjectType($query, string $subjectType)
    {
        return $query->where('subject_type', $subjectType);
    }

    /**
     * Get activity logs by action type
     */
    public function scopeWithAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get activity logs by IP address
     */
    public function scopeFromIp($query, string $ipAddress)
    {
        return $query->whereJsonContains('properties->ip_address', $ipAddress);
    }

    /**
     * Get activity summary by date
     */
    public function scopeDailySummary($query, $startDate = null, $endDate = null)
    {
        return $query->inDateRange($startDate, $endDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                'action',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'action')
            ->orderBy('date');
    }

    /**
     * Get activity summary by subject type
     */
    public function scopeSubjectSummary($query, $startDate = null, $endDate = null)
    {
        return $query->inDateRange($startDate, $endDate)
            ->select(
                'subject_type',
                'action',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('subject_type', 'action')
            ->orderBy('subject_type', 'action');
    }

    /**
     * Get most active users/IPs
     */
    public function scopeTopActors($query, $limit = 10, $startDate = null, $endDate = null)
    {
        return $query->inDateRange($startDate, $endDate)
            ->select(
                DB::raw('COALESCE(causer_id, properties->>"$.ip_address") as actor'),
                DB::raw('CASE WHEN causer_id IS NOT NULL THEN "user" ELSE "ip" END as actor_type'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('actor', 'actor_type')
            ->orderByDesc('count')
            ->limit($limit);
    }
}
