<?php

namespace ZanySoft\LaravelExceptionMonitor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ZanySoft\LaravelExceptionMonitor\ExceptionMonitor   notifyException(\Exception $e)
 */
class ExceptionMonitor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'exception-monitor';
    }
}
