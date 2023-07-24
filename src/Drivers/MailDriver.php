<?php

namespace ZanySoft\LaravelExceptionMonitor\Drivers;

use Illuminate\Contracts\Mail\Mailer;

class MailDriver implements DriverInterface
{

    protected $mailer;

    /**
     * MailDriver constructor.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * It sends e-mail notification for a given exception.
     *
     * @param \Exception $exception
     */
    public function send(\Exception $exception)
    {
        $config = config('exception-monitor.mail');

        $code = $exception->getCode();

        $subject = 'A exception has been thrown on ' . config('app.url');

        $title = $code ? $code . ' Exception' : 'Error Exception';

        $exception = $e = \ZanySoft\LaravelExceptionMonitor\Exception\FlattenException::create($exception);

        $this->mailer->send($config['view'], compact('exception', 'e', 'subject', 'title'), function ($m) use ($config, $subject) {
            $m->from($config['from']);
            $m->to($config['to']);
            $m->subject($subject);
        });
    }
}
