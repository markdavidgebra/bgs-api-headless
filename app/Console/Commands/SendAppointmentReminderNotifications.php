<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\Patient\AppointmentReminderPatientNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAppointmentReminderNotifications extends Command
{
    protected $signature = 'notifications:appointment-reminders';

    protected $description = 'Send email + portal reminders for appointments happening tomorrow';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $query = Appointment::query()
            ->with(['patient:id,name,email', 'doctor:id,name', 'service:id,name'])
            ->whereDate('appointment_date', $tomorrow)
            ->whereNull('reminder_sent_at')
            ->whereNotIn('status', ['cancelled', 'completed']);

        $count = 0;
        $query->chunkById(100, function ($appointments) use (&$count): void {
            foreach ($appointments as $appointment) {
                $patient = $appointment->patient;
                if (! $patient || blank($patient->email)) {
                    $appointment->update(['reminder_sent_at' => now()]);

                    continue;
                }

                Notification::send($patient, new AppointmentReminderPatientNotification($appointment));
                $appointment->update(['reminder_sent_at' => now()]);
                $count++;
            }
        });

        $this->info("Sent {$count} appointment reminder(s).");

        return self::SUCCESS;
    }
}
