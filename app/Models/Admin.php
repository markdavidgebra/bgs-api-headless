<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory, Notifiable;

    public const INVENTORY_ROLE = 'inventory_officer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'image_path',
        'status',
        'approved_at',
        'pending_password_plain',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isApproved(): bool
    {
        return strtolower((string) ($this->status ?? 'draft')) === 'approved';
    }

    public function hasRole(string ...$roles): bool
    {
        $role = strtolower((string) $this->role);
        $allowed = array_map(
            static fn (string $r): string => strtolower(trim($r)),
            $roles
        );

        return in_array($role, $allowed, true);
    }

    public function isInventoryOfficer(): bool
    {
        return strtolower((string) $this->role) === self::INVENTORY_ROLE;
    }

    public function scopeInventoryOfficers(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw('LOWER(role) = ?', [self::INVENTORY_ROLE]);
    }

    public function scopeNotInventoryOfficers(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw('LOWER(COALESCE(role, "")) <> ?', [self::INVENTORY_ROLE]);
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path === null || $this->image_path === '') {
            return null;
        }

        $path = (string) $this->image_path;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/admins/')) {
            return asset($normalized);
        }

        return asset('storage/'.$normalized);
    }
}
