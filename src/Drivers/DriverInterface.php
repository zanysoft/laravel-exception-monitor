<?php

namespace ZanySoft\LaravelExceptionMonitor\Drivers;

interface DriverInterface
{
    public function send($exception);
}
