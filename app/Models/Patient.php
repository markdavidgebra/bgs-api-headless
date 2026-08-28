<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Patient extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, Notifiable;

    /**
     * Patients authenticate against the legacy `users` table (Breeze / FKs).
     */
    protected $table = 'users';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_path',
        'password',
        'pending_password_plain',
        'status',
        'birthdate',
        'age',
        'gender',
        'address',
        'emergency_contact',
        'history_summary',
        'skin_type',
        'skin_concerns',
        'recovery_time',
        'max_appointments_per_day',
        'subscription',
        'notes',
        'appointment_history',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'age' => 'integer',
            'max_appointments_per_day' => 'integer',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ?? 'active';
    }

    public function getStatusBadgeAttribute(): string
    {
        return ($this->status ?? 'active') === 'active'
            ? 'bg-green-lt'
            : 'bg-secondary-lt';
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path === null || $this->avatar_path === '') {
            return null;
        }

        $path = (string) $this->avatar_path;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/patients/')) {
            return asset($normalized);
        }

        return asset('storage/'.$normalized);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id')
            ->orderByDesc('issued_at')
            ->orderByDesc('id');
    }

    public function doctorNotes(): HasMany
    {
        return $this->hasMany(DoctorNote::class, 'patient_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Latest appointment date (YYYY-MM-DD) from appointment_history when stored as JSON array of rows with "date".
     */
    public function latestAppointmentDateFromHistory(): ?string
    {
        if (blank($this->appointment_history)) {
            return null;
        }

        $data = json_decode($this->appointment_history, true);
        if (! is_array($data)) {
            return null;
        }

        $latest = null;
        foreach ($data as $row) {
            if (! is_array($row) || empty($row['date'])) {
                continue;
            }
            $date = (string) $row['date'];
            if ($latest === null || strcmp($date, $latest) > 0) {
                $latest = $date;
            }
        }

        return $latest;
    }
}
