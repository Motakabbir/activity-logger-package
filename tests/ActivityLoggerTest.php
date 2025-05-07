<?php

use PHPUnit\Framework\TestCase;

class ActivityLoggerTest extends TestCase
{
    protected $activityLogger;

    protected function setUp(): void
    {
        // Initialize the ActivityLogger instance before each test
        $this->activityLogger = new \ActivityLogger();
    }

    public function testLogActivity()
    {
        // Test logging an activity
        $this->activityLogger->log('User logged in', ['user_id' => 1]);

        $logs = $this->activityLogger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertEquals('User logged in', $logs[0]['message']);
    }

    public function testGetLogs()
    {
        // Test retrieving logs
        $this->activityLogger->log('User logged out', ['user_id' => 1]);
        $logs = $this->activityLogger->getLogs();

        $this->assertNotEmpty($logs);
        $this->assertEquals('User logged out', $logs[0]['message']);
    }

    public function testLogActivityWithContext()
    {
        // Test logging an activity with additional context
        $this->activityLogger->log('User updated profile', ['user_id' => 1, 'changes' => ['name' => 'New Name']]);

        $logs = $this->activityLogger->getLogs();
        $this->assertCount(1, $logs);
        $this->assertArrayHasKey('changes', $logs[0]['context']);
    }
}