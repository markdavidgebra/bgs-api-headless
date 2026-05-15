<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AffiliateCode extends Model
{
    protected $fillable = [
        'code',
        'label',
        'status',
        'effective_from',
        'effective_to',
        'discount_method',
        'discount_value',
        'notes',
        'times_used',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'discount_value' => 'decimal:2',
            'times_used' => 'integer',
        ];
    }

    public function recordUsage(): void
    {
        $this->increment('times_used');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'affiliate_code_service')
            ->withTimestamps();
    }

    public function treatmentPackages(): BelongsToMany
    {
        return $this->belongsToMany(TreatmentPackage::class, 'affiliate_code_treatment_package')
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'affiliate_code_product')
            ->withTimestamps();
    }

    public function membershipPlans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class, 'affiliate_code_membership_plan')
            ->withTimestamps();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-lt text-green',
            'inactive' => 'bg-secondary-lt text-secondary',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getDiscountLabelAttribute(): string
    {
        return match ($this->discount_method) {
            'percentage' => rtrim(rtrim((string) $this->discount_value, '0'), '.').'% off',
            'fixed' => '₱'.number_format((float) $this->discount_value, 2).' off',
            default => (string) $this->discount_value,
        };
    }

    protected function formatEffectivityDate(\Illuminate\Support\Carbon $date): string
    {
        $labels = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun.', 'Jul.', 'Aug.', 'Sep.', 'Oct.', 'Nov.', 'Dec.'];

        return $labels[(int) $date->format('n') - 1].' '.$date->format('j').', '.$date->format('Y');
    }

    public function getEffectivityLabelAttribute(): string
    {
        if (! $this->effective_from && ! $this->effective_to) {
            return '—';
        }

        $from = $this->effective_from ? $this->formatEffectivityDate($this->effective_from) : '—';
        $to = $this->effective_to ? $this->formatEffectivityDate($this->effective_to) : '—';

        return "{$from} – {$to}";
    }
}
