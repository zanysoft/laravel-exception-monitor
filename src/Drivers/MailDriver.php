<?php

namespace ZanySoft\LaravelExceptionMonitor\Drivers;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Str;
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

        $app_name = Str::title(config('app.name'));
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        $subject = 'A exception has been thrown on ' . $host;

        $title = $code ? $code . ' Exception' : 'Error Exception';

        $exception = $e = FlattenException::create($exception);

        $from = $config['from'] ?? '';
        if (empty($from)) {
            if (config('mail.from.address')) {
                $from = [config('mail.from.address') => config('mail.from.name')];
            } else {
                $femail = 'no-reply@' . $host;
                $from = [$femail => $app_name];
            }
        }

        $to = $config['to'] ?? '';
        if (!empty($to) && !is_array($to)) {
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
