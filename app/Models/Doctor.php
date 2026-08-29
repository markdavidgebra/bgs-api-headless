<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * The prescribing doctor. Separate guard/table from {@see ClinicalStaff}, which is
 * the nurse/therapist portal that used to be called "doctors".
 */
class Doctor extends Authenticatable
{
    use Notifiable;

    protected $table = 'doctors';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'admin_id',
        'name',
        'email',
        'password',
        'pending_password_plain',
        'phone',
        'specialty',
        'license_no',
        'prc_expiry',
        'ptr_no',
        's2_license_no',
        'signature_path',
        'bio',
        'image_path',
        'status',
        'approved_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pending_password_plain',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'prc_expiry' => 'date',
            'password' => 'hashed',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function scopeNotManagerAlias($query)
    {
        return $query->whereNull($this->getTable().'.admin_id');
    }

    public function isActive(): bool
    {
        return strtolower((string) ($this->status ?? 'pending')) === 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ?? 'pending';
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->image_path);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->signature_path);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'doctor_id')->orderByDesc('issued_at')->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DoctorNote::class, 'doctor_id')->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Public URL for a stored doctor asset. Supports absolute URLs, public/uploads/doctor-portal
     * and storage/linked files — mirrors {@see ClinicalStaff::profilePhotoUrl()}.
     */
    protected function publicFileUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/')) {
            return asset($normalized);
        }

        return asset('storage/'.$normalized);
    }
}
