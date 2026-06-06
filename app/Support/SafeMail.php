<?php

namespace App\Support;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SafeMail
{
    /**
     * Send mail without failing the surrounding request when delivery fails.
     */
    public static function send(string $to, Mailable $mailable): bool
    {
        if (! filled(config('mail.mailers.smtp.password'))) {
            Log::warning('Mail not sent: MAIL_PASSWORD is empty in .env.', [
                'to' => $to,
                'mailable' => $mailable::class,
            ]);

            return false;
        }

        try {
            Mail::to($to)->send($mailable);

            return true;
        } catch (TransportExceptionInterface $e) {
            report($e);

            Log::warning('Mail delivery failed.', [
                'to' => $to,
                'mailable' => $mailable::class,
                'host' => config('mail.mailers.smtp.host'),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
