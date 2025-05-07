<?php

namespace Loctracker\ActivityLogger\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupActivitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activitylog:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old activity logs';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Cleaning up old activity logs...');

        $keepLogsForDays = config('activity-logger.cleanup.keep_logs_for_days', 365);
        $chunkSize = config('activity-logger.cleanup.chunk_size', 1000);
        $model = config('activity-logger.activity_model');

        $cutOffDate = Carbon::now()->subDays($keepLogsForDays);

        $totalDeleted = 0;

        do {
            $deleted = $model::where('created_at', '<', $cutOffDate)
                ->take($chunkSize)
                ->delete();

            $totalDeleted += $deleted;

            if ($deleted > 0) {
                $this->info("Deleted {$deleted} records...");
            }
        } while ($deleted > 0);

        $this->info("Successfully cleaned up {$totalDeleted} old activity logs.");
    }
}
