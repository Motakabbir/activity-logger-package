<?php

namespace Loctracker\ActivityLogger;

use Illuminate\Support\ServiceProvider;
use Loctracker\ActivityLogger\Contracts\ActivityLoggerInterface;
use Loctracker\ActivityLogger\Http\Middleware\IgnoreLoggingRoute;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;

class ActivityLoggerServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        ActivityLoggerInterface::class => ActivityLogger::class,
    ];

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
            return new ActivityLogger($app['config']->get('activity-logger', []));
        });

        // Set up queue configuration if enabled
        if ($this->app['config']->get('activity-logger.queue.enabled', false)) {
            $this->configureQueue();
        }
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        $this->registerMiddleware();
        $this->registerEventListeners();
        $this->registerCommands();
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/Config/activity-logger.php' => config_path('activity-logger.php'),
        ], 'activity-logger-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('migrations'),
        ], 'activity-logger-migrations');

        if (!$this->app->runningUnitTests()) {
            $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        }
    }

    /**
     * Register the package middleware.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        if (!empty($this->app['config']->get('activity-logger.ignore_routes', []))) {
            $router = $this->app['router'];

            // Register the middleware
            $router->aliasMiddleware('activity.ignore', IgnoreLoggingRoute::class);

            // Add to web middleware group if it exists
            if ($router->hasMiddlewareGroup('web')) {
                $router->pushMiddlewareToGroup('web', 'activity.ignore');
            }
        }
    }

    /**
     * Register the package commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if (
            $this->app->runningInConsole() &&
            $this->app['config']->get('activity-logger.cleanup.enabled', false)
        ) {
            $this->commands([
                Console\CleanupActivitiesCommand::class,
            ]);
        }
    }

    /**
     * Register authentication event listeners.
     *
     * @return void
     */
    protected function registerEventListeners()
    {
        if (!$this->app['config']->get('activity-logger.log_auth_events', false)) {
            return;
        }

        Event::listen(Login::class, function ($event) {
            app('activitylogger')->log(
                'auth.login',
                'User logged in successfully',
                $event->user,
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );
        });

        Event::listen(Logout::class, function ($event) {
            app('activitylogger')->log(
                'auth.logout',
                'User logged out',
                $event->user,
                [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );
        });

        Event::listen(Failed::class, function ($event) {
            app('activitylogger')->log(
                'auth.failed',
                'Failed login attempt',
                null,
                [
                    'ip' => request()->ip(),
                    'email' => $event->credentials['email'] ?? null,
                    'user_agent' => request()->userAgent(),
                ]
            );
        });
    }

    /**
     * Configure the queue connection for activity logging.
     *
     * @return void
     */
    protected function configureQueue()
    {
        $config = $this->app['config'];

        $config->set('queue.connections.activity-logs', [
            'driver' => $config->get('activity-logger.queue.connection', 'sync'),
            'queue' => $config->get('activity-logger.queue.queue', 'activity-logs'),
            'retry_after' => 90,
            'after_commit' => true,
        ]);
    }
}
