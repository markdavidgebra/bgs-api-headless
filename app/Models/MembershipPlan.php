<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MembershipPlan extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipPlanFactory> */
    use HasFactory;

    protected $table = 'membership_plans';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'price',
        'status',
        'billing_cycle',
        'duration_value',
        'duration_type',
        'max_usage_per_month',
        'rollover_unused_sessions',
        'cancellation_allowed',
        'pause_allowed',
        'terms_and_conditions',
        'before_care',
        'aftercare',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_value' => 'integer',
            'max_usage_per_month' => 'integer',
            'rollover_unused_sessions' => 'boolean',
            'cancellation_allowed' => 'boolean',
            'pause_allowed' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'membership_plan_service',
            'membership_plan_id',
            'service_id'
        )->withPivot('sessions')->withTimestamps();
    }

    public function patientSubscriptions(): HasMany
    {
        return $this->hasMany(PatientSubscription::class, 'membership_plan_id');
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(
            Promotion::class,
            'membership_plan_promotion',
            'membership_plan_id',
            'promotion_id'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-lt text-green',
            'inactive' => 'bg-secondary-lt text-secondary',
            'draft' => 'bg-yellow-lt text-yellow',
            'archived' => 'bg-red-lt text-red',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        return null;
    }

    public function getDurationLabelAttribute(): ?string
    {
        if (! $this->duration_value || ! $this->duration_type) {
            return null;
        }

        return $this->duration_value.' '.Str::plural($this->duration_type, $this->duration_value);
    }

    public function getTotalSessionsAttribute(): int
    {
        return (int) $this->services->sum(function ($service) {
            return (int) ($service->pivot->sessions ?? 0);
        });
    }

    public function getIncludedBenefitsLabelAttribute(): string
    {
        if (! $this->relationLoaded('services') && ! $this->services()->exists()) {
            return '—';
        }

        return $this->services
            ->map(function ($service) {
                $sessions = (int) ($service->pivot->sessions ?? 0);

                return $sessions > 0
                    ? "{$service->name} ({$sessions})"
                    : $service->name;
            })
            ->implode(', ');
    }

    public function getActiveSubscribersCountAttribute(): int
    {
        return $this->patientSubscriptions()
            ->where('status', 'active')
            ->count();
    }

    public function getNotesAttribute(): ?string
    {
        return $this->internal_notes;
    }
}
