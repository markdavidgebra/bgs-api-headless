<?php

namespace App\Models;

use App\Models\Concerns\AliasesLegacyStaffId;
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
    use AliasesLegacyStaffId;

    protected $fillable = [
        'appointment_no',
        'patient_id',
        'clinical_staff_id',
        'doctor_id',
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(ClinicalStaff::class, 'clinical_staff_id');
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

    public function getDoctorNameAttribute()
    {
        return $this->doctor?->name ?? '—';
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
