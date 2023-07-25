<?php

namespace ZanySoft\LaravelExceptionMonitor\Drivers;

use Carbon\Carbon;
use Maknz\Slack\Attachment;
use Maknz\Slack\AttachmentField;
use Maknz\Slack\Client as Slack;

class SlackDriver implements DriverInterface
{

    protected $slack;


    /**
     * SlackDriver constructor.
     *
     * @param Slack $slack
     */
    public function __construct(Slack $slack)
    {
        $this->slack = $slack;
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    public function send($exception)
    {
        $config     = config('exception-monitor.slack');
        $message    = 'Exception has been thrown on `' . config('app.url') . '`';
        $attachment = new Attachment([
            'color'  => 'danger',
            'fields' => [
                new AttachmentField([
                    'title' => 'Message',
                    'value' => $exception->getMessage(),
                    'short' => true
                ]),
                new AttachmentField([
                    'title' => 'File',
                    'value' => $exception->getFile() . ':' . $exception->getLine(),
                    'short' => true
                ]),

                new AttachmentField([
                    'title' => 'Request',
                    'value' => app('request')->getRequestUri(),
                    'short' => true
                ]),
                new AttachmentField([
                    'title' => 'Timestamp',
                    'value' => Carbon::now()->toDateTimeString(),
                    'short' => true
                ]),
                new AttachmentField([
                    'title' => 'User',
                    'value' => auth()->check() ? auth()->user()->id : 'Not logged in',
                    'short' => true
                ])
            ]
        ]);

        $this->slack->to($config['channel'])->from($config['username'])->attach($attachment)->withIcon($config['icon'])->send($message);
    }
}