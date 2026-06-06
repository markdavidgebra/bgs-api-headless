<?php

namespace App\Console\Commands;

use App\Mail\PatientRegistrationApprovedMail;
use App\Support\SafeMail;
use Illuminate\Console\Command;

class SendTestMailCommand extends Command
{
    protected $signature = 'mail:test {to : Recipient email address}';

    protected $description = 'Send a test patient approval email using current MAIL_* settings';

    public function handle(): int
    {
        $to = (string) $this->argument('to');

        $this->line('Mailer: '.config('mail.default'));
        $this->line('Host: '.config('mail.mailers.smtp.host'));
        $this->line('Username: '.config('mail.mailers.smtp.username'));
        $this->line('From: '.config('mail.from.address'));

        if (! filled(config('mail.mailers.smtp.password'))) {
            $this->error('MAIL_PASSWORD is empty. Set the password for admin@bioglowsolutions.com in .env first.');

            return self::FAILURE;
        }

        $sent = SafeMail::send(
            $to,
            new PatientRegistrationApprovedMail(
                name: 'Test Patient',
                emailAddress: $to,
                plainPassword: 'test-password-change-me'
            )
        );

        if (! $sent) {
            $this->error('Mail could not be sent. Check storage/logs/laravel.log for details.');

            return self::FAILURE;
        }

        $this->info("Test email sent to {$to}.");

        return self::SUCCESS;
    }
}
