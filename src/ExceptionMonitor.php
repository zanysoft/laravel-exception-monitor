<?php

namespace ZanySoft\LaravelExceptionMonitor;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionMonitor
{

    /**
     * It calls all enabled drivers and triggers requests to send notifications.
     *
     * @param \Exception $exception
     */
    public function notifyException($exception)
    {
        $code = $this->errorCodeFromException($exception);

        if ($exception instanceof TokenMismatchException) {
            return;
        }

        if ($exception instanceof ModelNotFoundException) {
            return;
        }

        $skip_error = config('exception-monitor.skip_error');

        if (!is_array($skip_error)) {
            $skip_error = array_map('trim', explode(',', $skip_error));
        }

        if (in_array($code, $skip_error)) {
            return;
        }

        $drivers = config('exception-monitor.drivers');

        if ($this->enabledEnvironment(app()->environment())) {
            if (!is_array($drivers)) {
                $drivers = explode(',', $drivers);
            }

            foreach ($drivers as $driver) {
                $this->sendException($exception, $driver);
            }
        }
    }

    /**
     * @param \Throwable $exception
     * @return int
     */
    protected function errorCodeFromException($exception)
    {
        if ($this->isHttpException($exception)) {
            return $exception->getStatusCode();
        }

        return $exception->getCode();
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param \Throwable $exception
     * @return bool
     */
    protected function isHttpException($exception)
    {
        return $exception instanceof HttpExceptionInterface;
    }

    /**
     * Check if given environment is enabled in configuration file.
     *
     * @param $environment
     *
     * @return mixed
     */
    protected function enabledEnvironment($environment)
    {
        $environments = config('exception-monitor.environments');

        if (is_array($environments)) {
            return in_array($environment, $environments);
        }

        return $environment === $environments;
    }

    /**
     * It sends notification to given driver.
     *
     * @param \Exception $e
     * @param            $driver
     */
    protected function sendException($e, $driver)
    {
        $channel = $this->getDriverInstance($driver);
        $channel->send($e);
    }

    /**
     * It injects driver's class to Laravel application.
     *
     * @param $driver
     *
     * @return mixed
     */
    protected function getDriverInstance($driver)
    {
        $class = '\ZanySoft\LaravelExceptionMonitor\Drivers\\' . ucfirst(trim($driver)) . 'Driver';

        return app($class);
    }
}