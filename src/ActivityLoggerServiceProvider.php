<?php

namespace Loctracker\ActivityLogger;

use Illuminate\Support\ServiceProvider;
use Loctracker\ActivityLogger\Contracts\ActivityLoggerInterface;

class ActivityLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/activity-logger.php',
            'activity-logger'
        );

        $this->app->singleton('activitylogger', function ($app) {
            return new ActivityLogger();
        });

        $this->app->bind(ActivityLoggerInterface::class, ActivityLogger::class);
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Load the configuration file
        $this->publishes([
            __DIR__ . '/Config/activity-logger.php' => config_path('activity-logger.php'),
        ], 'config');        // Load migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Migrations' => database_path('migrations'),
            ], 'migrations');

            if (!$this->app->runningUnitTests()) {
                $this->loadMigrationsFrom(__DIR__ . '/Migrations');
            }
        }
    }
}
