<?php

namespace ActivityLogger\Facades;

use Illuminate\Support\Facades\Facade;

class ActivityLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'activitylogger';
    }
}