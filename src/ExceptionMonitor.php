<?php

namespace ZanySoft\LaravelExceptionMonitor;

class ExceptionMonitor
{

    /**
     * It calls all enabled drivers and triggers requests to send notifications.
     *
     * @param \Exception $e
     */
    public function notifyException($e)
    {
        $drivers = config('exception-monitor.drivers');

        if ($this->enabledEnvironment(app()->environment())) {
            if (!is_array($drivers)) {
                $drivers = explode(',', $drivers);
            }

            foreach ($drivers as $driver) {
                $this->sendException($e, $driver);
            }
        }
    }

    /**
     * It sends notification to given driver.
     *
     * @param \Exception $e
     * @param            $driver
     */
    protected function sendException( $e, $driver)
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
}