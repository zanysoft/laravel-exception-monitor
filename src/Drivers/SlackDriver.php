<?php

namespace ZanySoft\LaravelExceptionMonitor\Drivers;

use Carbon\Carbon;
use ZanySoft\LaravelExceptionMonitor\Lib\Slack;

class SlackDriver implements DriverInterface
{

    /**
     * @var Slack
     */
    protected $slack;

    protected $config;

    /**
     * SlackDriver constructor.
     */
    public function __construct()
    {
        $this->config = config('exception-monitor.slack');

        $this->slack = new Slack($this->config['endpoint']);
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    public function send($exception)
    {
        $config = $this->config;
        $message = 'Exception has been thrown on `' . config('app.url') . '`';
        $attachment = [
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'Message',
                    'value' => $exception->getMessage(),
                    'short' => false
                ],
                [
                    'title' => 'File',
                    'value' => $exception->getFile() . ':' . $exception->getLine(),
                    'short' => false
                ],
                [
                    'title' => 'Request',
                    'value' => strtolower(app('request')->getMethod()) . ': ' . app('request')->url(),
                    'short' => false
                ],
                [
                    'title' => 'Timestamp',
                    'value' => Carbon::now()->toDateTimeString(),
                    'short' => true
                ],
                [
                    'title' => 'User',
                    'value' => auth()->check() ? auth()->user()->id : 'Not logged in',
                    'short' => true
                ]
            ]
        ];

        $this->slack->to($config['channel'])->from($config['username'])->attach($attachment)->withIcon($config['icon'])->send($message);
    }
}