<?php

namespace App\Console\Commands;

use App\Mail\AdminApprovalQueueDigestMail;
use App\Models\Admin;
use App\Models\ClinicalStaff;
use App\Models\Patient;
use App\Support\AdminNotificationRecipients;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAdminApprovalQueueDigestCommand extends Command
{
    protected $signature = 'notifications:admin-approval-digest';

    protected $description = 'Daily email digest for pending approvals (patients, doctors, staff)';

    public function handle(): int
    {
        $pendingPatients = Patient::query()->where('status', 'pending')->count();
        $pendingDoctors = ClinicalStaff::query()->where('status', 'pending')->count();
        $draftStaff = Admin::query()->where('status', 'draft')->count();

        $regEmails = AdminNotificationRecipients::emailsForPermission('registrations.manage');
        $superEmails = AdminNotificationRecipients::superAdminEmails();
        $emails = array_values(array_unique(array_merge($regEmails, $superEmails)));

        if ($emails === []) {
            $this->warn('No admin recipients for approval digest.');

            return self::SUCCESS;
        }

        if ($pendingPatients === 0 && $pendingDoctors === 0 && $draftStaff === 0) {
            $this->info('Nothing pending; digest skipped.');

            return self::SUCCESS;
        }

        Mail::to($emails)->send(new AdminApprovalQueueDigestMail(
            pendingPatients: $pendingPatients,
            pendingDoctors: $pendingDoctors,
            draftStaff: $draftStaff,
        ));
        $this->info('Approval digest sent.');

        return self::SUCCESS;
    }
}
