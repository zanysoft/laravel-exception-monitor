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
        $user = 'Not logged in';
        if (auth()->check()) {
            try {
                $columns = auth()->user()->getAttributes();
                foreach (['name', 'fullname', 'firstname', 'first_name'] as $col) {
                    if (array_key_exists($col, $columns)) {
                        $user = $columns[$col] . ' (' . auth()->user()->id . ')';
                        break;
                    }
                }
            } catch (\Exception $e) {
                $user = auth()->user()->id;
            }
        }

        $file = str_replace('\\', '/', $exception->getFile());
        $root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $file = str_replace($root, '', $file);

        $error = $exception->getMessage();
        $request = app('request')->url();
        $request = request()->getRequestUri();

        $config = $this->config;
        $message = '*Exception has been thrown on `' . config('app.url') . '`* ';
        $attachment = [
            'color' => 'danger',
            'title' => 'Message',
            'text' => $error,
            'fields' => [
                [
                    'title' => 'File',
                    'value' => $file . ':' . $exception->getLine(),
                    'short' => false
                ],
                [
                    'title' => 'Request',
                    'value' => strtolower(app('request')->getMethod()) . ':' . $request,
                    'short' => false
                ],
                [
                    'title' => 'Timesramp',
                    'value' => Carbon::now()->toDateTimeString(),
                    'short' => true
                ],
                [
                    'title' => 'User',
                    'value' => $user,
                    'short' => true
                ]
            ]
        ];

        $this->slack->to($config['channel'])->from($config['username'])->attach($attachment)->withIcon($config['icon'])->send($message);
    }
}