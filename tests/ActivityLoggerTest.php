<?php

namespace Tests;

use Loctracker\ActivityLogger\ActivityLogger;
use Orchestra\Testbench\TestCase;
use Tests\TestModel;

class ActivityLoggerTest extends TestCase
{
    protected $activityLogger;
    protected function setUp(): void
    {
        parent::setUp();
        $this->activityLogger = new ActivityLogger();
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
    protected function getPackageProviders($app)
    {
        return ['Loctracker\ActivityLogger\ActivityLoggerServiceProvider'];
    }
    protected function defineDatabaseMigrations()
    {
        // Run migrations manually to avoid class name conflicts
        include_once __DIR__ . '/../src/Migrations/2024_01_01_create_activity_logs_table.php';
        include_once __DIR__ . '/migrations/2024_01_01_create_test_models_table.php';

        (new \Loctracker\ActivityLogger\Database\Migrations\ActivityLoggerCreateActivityLogsTable)->up();
        (new \Tests\Database\Migrations\CreateTestModelsTable)->up();
    }

    public function testLogActivity()
    {
        // Test logging an activity
        $activity = $this->activityLogger->log('user.login', 'User logged in');

        $this->assertNotNull($activity);
        $this->assertEquals('user.login', $activity->action);
        $this->assertEquals('User logged in', $activity->description);
    }

    public function testLogActivityWithProperties()
    {
        // Test logging an activity with properties
        $properties = ['ip' => '127.0.0.1'];
        $activity = $this->activityLogger->withProperties($properties)
            ->log('user.login', 'User logged in');

        $this->assertEquals($properties, $activity->properties);
    }
    public function testLogActivityWithSubject()
    {
        // Create a test model instance
        $user = TestModel::create(['name' => 'Test User']);

        // Test logging an activity with a subject
        $activity = $this->activityLogger
            ->log('user.update', 'Profile updated', $user);

        $this->assertEquals(TestModel::class, $activity->subject_type);
        $this->assertEquals($user->id, $activity->subject_id);
    }
}
