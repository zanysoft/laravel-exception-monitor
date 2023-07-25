<?php

namespace ZanySoft\LaravelExceptionMonitor\Drivers;

use Illuminate\Contracts\Mail\Mailer;
use ZanySoft\LaravelExceptionMonitor\Exception\FlattenException;

class MailDriver implements DriverInterface
{

    /**
     * @var Mailer
     */
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
    public function send($exception)
    {
        $config = config('exception-monitor.mail');

        $code = $exception->getCode();

        $subject = 'A exception has been thrown on ' . config('app.url');

        $title = $code ? $code . ' Exception' : 'Error Exception';

        $exception = $e = FlattenException::create($exception);

        $from = $config['from'] ?? '';
        if (!$from) {
            $from = [config('mail.from.address') => config('mail.from.name')];
        }

        $to = $config['to'];
        if (!is_array($to)) {
            $to = preg_replace('/\s+/', '', $to);
            $to = explode(',', $to);
        }

        $this->mailer->send($config['view'], compact('exception', 'e', 'subject', 'title'), function ($m) use ($from, $to, $subject) {
            $m->from($from);
            $m->to($to);
            $m->subject($subject);
        });
    }
}
