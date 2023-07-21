<?php

namespace ZanySoft\LaravelExceptionMonitor;

use ZanySoft\LaravelExceptionMonitor\ExceptionMonitor;
use Illuminate\Support\ServiceProvider;

class ExceptionMonitorServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerResources();
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'exception-monitor');
    }


    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/exception-monitor.php', 'exception-monitor');

        $this->app->singleton('exception-monitor', function () {
            return new ExceptionMonitor;
        });
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['exception-monitor'];
    }

    /**
     * Register currency resources.
     *
     * @return void
     */
    public function registerResources()
    {
        if ($this->isLumen() === false) {
            $this->publishes([
                __DIR__ . '/../config/exception-monitor.php' => config_path('exception-monitor.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/exception-monitor'),
            ], 'views');
        }
    }


    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }
}
