Laravel Exception Monitor
=========================

[![Laravel](https://img.shields.io/badge/Laravel-5.x-orange.svg?style=flat-square)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)
[![Downloads](https://img.shields.io/packagist/dt/zanysoft/laravel-exception-monitor.svg?style=flat-square)](https://packagist.org/packages/zanysoft/laravel-exception-monitor)
[![GitHub tag](https://img.shields.io/github/tag/ZanySoft/laravel-exception-monitor.svg?style=flat&color=informational)](https://github.com/zanysoft/laravel-exception-monitor/tags)

This package notifies you when exceptions are thrown on some of your production application.

![Slack Preview](/preview.png)

#### Installation

```bash
composer require zanysoft/laravel-exception-monitor
```

If you’re on Laravel 5.4 or earlier, you’ll need to add the following to your `config/app.php` (for Laravel 5.5 and up these will be auto-discovered by Laravel):

```php
'providers' => [
    //...
    ZanySoft\LaravelExceptionMonitor\ExceptionMonitorServiceProvider::class,
],

'aliases' => [
    //...
    'ExceptionMonitor' => ZanySoft\LaravelExceptionMonitor\Facades\ExceptionMonitor::class,
];
```

Publish the package config and view files to your application. Run these commands inside your terminal.

```
php artisan vendor:publish --provider="ZanySoft\LaravelExceptionMonitor\ExceptionMonitorServiceProvider"
```

You also have to make sure if you have [makzn/slack](https://github.com/maknz/slack) package installed and configured properly for Slack notifications.

#### Configuration

Config File is pretty self-explanatory.

```php
<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Enabled sender drivers
     |--------------------------------------------------------------------------
     |
     | Send a notification about exception in your application to supported channels.
     |
     | Supported: "mail", "slack". You can use multiple drivers.
     |
     */
    'drivers'      => [ 'mail', 'slack' ],

    /*
     |--------------------------------------------------------------------------
     | Enabled application environments
     |--------------------------------------------------------------------------
     |
     | Set environments that should generate notifications.
     |
     */
    'environments' => [ 'production'],

    /*
     |--------------------------------------------------------------------------
     | Disable Error Notifications
     |--------------------------------------------------------------------------
     |
     | Set status code for disable notifications. like [401,404,500] 
     |
     */
    'skip_error' => [401, 404, 405, 500],

    /*
     |--------------------------------------------------------------------------
     | Mail Configuration
     |--------------------------------------------------------------------------
     |
     | It uses your app default Mail driver. You shouldn't probably touch the view
     | property unless you know what you're doing.
     |
     */
    'mail'         => [
        'from' => 'sender@example.com',
        'to'   => 'recipient@example.com',
        'view' => 'mails/exception-monitor'
    ],

    /*
     * Uses maknz\slack package.
     */
    'slack'        => [
        'channel'  => '#bugtracker',
        'username' => 'Exception Monitor',
        'icon'     => ':robot_face:',
    ],
];
```

#### Usage

To start catching exceptions you have 2 options out there.

**First option**: Extend from Exception Handler provided by package (`app/Exceptions/Handler.php`):

```php
use ZanySoft\LaravelExceptionMonitor\MonitorExceptionHandler;
...
class Handler extends MonitorExceptionHandler
```

**Second option**: Make your `report` method in `app/Exceptions/Handler.php` to look like this:

```php
public function report(Exception $e)
{
    foreach ($this->dontReport as $type) {
        if ($e instanceof $type) {
            return parent::report($e);
        }
    }

    if (app()->bound('exception-monitor')) {
        app('exception-monitor')->notifyException($e);
    }
  
    // OR
  
    ExceptionMonitor::notifyException($e);

    parent::report($e);
}
```

#### License

This library is licensed under the MIT license. Please see [License file](LICENSE.md) for more information.
