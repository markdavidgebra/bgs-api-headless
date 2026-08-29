<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'appointment_no',
        'patient_id',
        'clinical_staff_id',
        'assigned_admin_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'status',
        'reminder_sent_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function clinicalStaff(): BelongsTo
    {
        return $this->belongsTo(ClinicalStaff::class, 'clinical_staff_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function note(): HasOne
    {
        return $this->hasOne(AppointmentNote::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(AppointmentTimeline::class)->orderByDesc('event_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AppointmentPayment::class)->orderByDesc('id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'appointment_id')->orderByDesc('id');
    }

    public function doctorNotes(): HasMany
    {
        return $this->hasMany(DoctorNote::class, 'appointment_id')->orderByDesc('id');
    }

    /**
     * Products prescribed during this visit (doctor treatment notes).
     */
    public function prescribedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'appointment_product')
            ->withPivot(['quantity'])
            ->withTimestamps();
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status ?? 'pending') {
            'confirmed' => 'bg-blue-lt',
            'completed' => 'bg-green-lt',
            'cancelled' => 'bg-red-lt',
            'rescheduled' => 'bg-azure-lt',
            default => 'bg-yellow-lt',
        };
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status ?? 'pending');
    }

    public function getPatientNameAttribute()
    {
        return $this->patient?->name ?? '—';
    }

    public function getClinicalStaffNameAttribute()
    {
        return $this->clinicalStaff?->name
            ?? $this->assignedAdmin?->name
            ?? '—';
    }

    public function getServiceNameAttribute()
    {
        return $this->service?->name ?? '—';
    }

    public function getDateDisplayAttribute()
    {
        return $this->appointment_date?->format('Y-m-d') ?? '—';
    }

    public function getTimeDisplayAttribute()
    {
        $timeRaw = $this->appointment_time;

        return $timeRaw
            ? (is_string($timeRaw) && strlen($timeRaw) >= 8
                ? substr($timeRaw, 0, 5)
                : \Illuminate\Support\Carbon::parse($timeRaw)->format('H:i'))
            : '—';
    }
}
