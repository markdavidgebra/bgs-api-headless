<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class RepairPatientRegistrationPasswords extends Command
{
    protected $signature = 'patients:repair-passwords
                            {--email= : Fix a single patient by email}
                            {--password= : Plain password to set (with --email)}';

    protected $description = 'Re-hash patient passwords once (fixes double-hashed registration bug)';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        if ($email) {
            if (! is_string($password) || $password === '') {
                $this->error('Provide --password when using --email.');

                return self::FAILURE;
            }

            $patient = Patient::query()->where('email', $email)->first();
            if (! $patient) {
                $this->error("No patient found for {$email}.");

                return self::FAILURE;
            }

            $patient->forceFill(['password' => $password])->save();
            $this->info("Password reset for {$email}.");

            return self::SUCCESS;
        }

        $fixed = 0;
        Patient::query()
            ->whereNotNull('pending_password_plain')
            ->where('pending_password_plain', '!=', '')
            ->orderBy('id')
            ->each(function (Patient $patient) use (&$fixed): void {
                try {
                    $plain = Crypt::decryptString((string) $patient->pending_password_plain);
                } catch (\Throwable) {
                    $this->warn("Skipping {$patient->email}: could not decrypt pending password.");

                    return;
                }

                if ($plain === '') {
                    return;
                }

                $patient->forceFill(['password' => $plain])->save();
                $this->line("Fixed {$patient->email}");
                $fixed++;
            });

        $this->info("Repaired {$fixed} patient password(s) from pending_password_plain.");

        return self::SUCCESS;
    }
}
