<?php

namespace App\Notifications\Patient;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-portal notice when a password reset link is sent (email is sent separately by Laravel).
 */
class PatientPasswordResetLinkSentNotification extends Notification
{
    use Queueable;

    public function __construct() {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'password_reset_requested',
            'title' => __('Password reset requested'),
            'message' => __('If an account exists for this email, we sent a reset link. Check your inbox and spam folder.'),
            'action_url' => route('login'),
        ];
    }
}
