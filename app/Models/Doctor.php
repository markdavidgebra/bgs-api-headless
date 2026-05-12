<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Doctor extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\DoctorFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'pending_password_plain',
        'phone',
        'specialty',
        'license_no',
        'experience_years',
        'bio',
        'image_path',
        'social_links',
        'status',
        'approved_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'experience_years' => 'integer',
            'social_links' => 'array',
        ];
    }

    public function getStatusLabelAttribute()
    {
        return $this->status ?? 'active';
    }

    public function getStatusBadgeAttribute()
    {
        $status = strtolower((string) ($this->status ?? 'pending'));

        return match ($status) {
            'active' => 'bg-green-lt',
            'inactive' => 'bg-red-lt',
            default => 'bg-secondary-lt',
        };
    }

    public function getInitialAttribute()
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->profilePhotoUrl();
    }

    /**
     * Public URL for the doctor profile photo. Supports absolute URLs, public/uploads/doctors,
     * legacy public/doctor/profile paths, and storage/linked files.
     */
    public function profilePhotoUrl(): ?string
    {
        if ($this->image_path === null || $this->image_path === '') {
            return null;
        }

        $path = (string) $this->image_path;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/doctors/')) {
            return asset($normalized);
        }

        if (str_starts_with($normalized, 'doctor/profile/')) {
            return asset($normalized);
        }

        return asset('storage/'.$normalized);
    }

    public function weeklySchedules(): HasMany
    {
        return $this->hasMany(DoctorWeeklySchedule::class)->orderBy('weekday');
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(DoctorBlockedDate::class)->orderBy('blocked_date');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DoctorNotification::class)->orderByDesc('created_at');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'doctor_service')->withTimestamps();
    }
}
