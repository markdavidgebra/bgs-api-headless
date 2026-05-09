<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientSubscription extends Model
{
    /** @use HasFactory<\Database\Factories\PatientSubscriptionFactory> */
    use HasFactory;

    protected $table = 'patient_subscriptions';

    protected $fillable = [
        'patient_id',
        'membership_plan_id',
        'start_date',
        'renewal_date',
        'end_date',
        'status',
        'sessions_used',
        'sessions_remaining',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'patient_id' => 'integer',
            'membership_plan_id' => 'integer',
            'start_date' => 'date',
            'renewal_date' => 'date',
            'end_date' => 'date',
            'sessions_used' => 'integer',
            'sessions_remaining' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-lt text-green',
            'expired' => 'bg-yellow-lt text-yellow',
            'cancelled' => 'bg-red-lt text-red',
            'paused' => 'bg-blue-lt text-blue',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getUsageLabelAttribute(): string
    {
        $used = (int) $this->sessions_used;
        $remaining = (int) $this->sessions_remaining;
        $total = $used + $remaining;

        return "{$used} / {$total}";
    }

    public function getRemainingUsageLabelAttribute(): string
    {
        return (string) ((int) $this->sessions_remaining);
    }

    public function getRenewalStatusAttribute(): string
    {
        if (! $this->renewal_date) {
            return 'no_renewal_date';
        }

        $today = now()->startOfDay();

        if ($this->renewal_date->isPast() && $this->status !== 'active') {
            return 'overdue';
        }

        if ($this->renewal_date->isToday()) {
            return 'due_today';
        }

        if ($this->renewal_date->diffInDays($today, false) >= -7) {
            return 'upcoming';
        }

        return 'normal';
    }

    public function getRenewalStatusLabelAttribute(): string
    {
        return match ($this->renewal_status) {
            'overdue' => 'Overdue',
            'due_today' => 'Due Today',
            'upcoming' => 'Upcoming',
            'no_renewal_date' => 'No Renewal Date',
            default => 'Normal',
        };
    }

    public function getRenewalStatusBadgeAttribute(): string
    {
        return match ($this->renewal_status) {
            'overdue' => 'bg-red-lt text-red',
            'due_today' => 'bg-yellow-lt text-yellow',
            'upcoming' => 'bg-blue-lt text-blue',
            'no_renewal_date' => 'bg-secondary-lt text-secondary',
            default => 'bg-green-lt text-green',
        };
    }
}
