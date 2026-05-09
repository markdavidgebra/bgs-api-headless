<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNotification extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorNotificationFactory> */
    use HasFactory;

    public const TYPE_NEW_APPOINTMENT = 'new_appointment';

    public const TYPE_CANCELLED_APPOINTMENT = 'cancelled_appointment';

    public const TYPE_RESCHEDULED_APPOINTMENT = 'rescheduled_appointment';

    public const TYPE_UPCOMING_REMINDER = 'upcoming_reminder';

    public const TYPE_FOLLOW_UP_REMINDER = 'follow_up_reminder';

    public const TYPE_PATIENT_NOTE_REMINDER = 'patient_note_reminder';

    /** @var list<string> */
    public const TYPES = [
        self::TYPE_NEW_APPOINTMENT,
        self::TYPE_CANCELLED_APPOINTMENT,
        self::TYPE_RESCHEDULED_APPOINTMENT,
        self::TYPE_UPCOMING_REMINDER,
        self::TYPE_FOLLOW_UP_REMINDER,
        self::TYPE_PATIENT_NOTE_REMINDER,
    ];

    /** @var list<string> */
    public const APPOINTMENT_TYPES = [
        self::TYPE_NEW_APPOINTMENT,
        self::TYPE_CANCELLED_APPOINTMENT,
        self::TYPE_RESCHEDULED_APPOINTMENT,
    ];

    /** @var list<string> */
    public const FOLLOW_UP_TYPES = [
        self::TYPE_FOLLOW_UP_REMINDER,
        self::TYPE_PATIENT_NOTE_REMINDER,
    ];

    /** @var list<string> */
    public const REMINDER_TYPES = [
        self::TYPE_UPCOMING_REMINDER,
    ];

    protected $fillable = [
        'doctor_id',
        'type',
        'title',
        'message',
        'read_at',
        'appointment_id',
        'patient_id',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'unread' => $query->unread(),
            'appointments' => $query->whereIn('type', self::APPOINTMENT_TYPES),
            'follow_ups' => $query->whereIn('type', self::FOLLOW_UP_TYPES),
            'reminders' => $query->whereIn('type', self::REMINDER_TYPES),
            default => $query,
        };
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getIconClassAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_NEW_APPOINTMENT => 'fa-solid fa-calendar-plus text-primary',
            self::TYPE_CANCELLED_APPOINTMENT => 'fa-solid fa-calendar-xmark text-danger',
            self::TYPE_RESCHEDULED_APPOINTMENT => 'fa-solid fa-calendar-day text-warning',
            self::TYPE_UPCOMING_REMINDER => 'fa-solid fa-bell text-info',
            self::TYPE_FOLLOW_UP_REMINDER => 'fa-solid fa-user-clock text-secondary',
            self::TYPE_PATIENT_NOTE_REMINDER => 'fa-solid fa-file-medical text-success',
            default => 'fa-solid fa-circle-info text-muted',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_NEW_APPOINTMENT => 'Appointment',
            self::TYPE_CANCELLED_APPOINTMENT => 'Appointment',
            self::TYPE_RESCHEDULED_APPOINTMENT => 'Appointment',
            self::TYPE_UPCOMING_REMINDER => 'Reminder',
            self::TYPE_FOLLOW_UP_REMINDER => 'Follow-up',
            self::TYPE_PATIENT_NOTE_REMINDER => 'Follow-up',
            default => 'Notice',
        };
    }

    public function primaryActionUrl(): ?string
    {
        if ($this->appointment_id) {
            return route('doctor.appointments.show', ['appointment' => $this->appointment_id]);
        }
        if ($this->patient_id) {
            return route('doctor.patient-records.show', ['patient' => $this->patient_id]);
        }

        return null;
    }

    public function primaryActionLabel(): string
    {
        if ($this->appointment_id) {
            return __('View appointment');
        }
        if ($this->patient_id) {
            return __('View patient');
        }

        return __('Open');
    }
}
