<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'name',
        'code',
        'type',
        'status',
        'description',
        'image',
        'discount_value',
        'discount_method',
        'minimum_spend',
        'maximum_discount',
        'applies_to',
        'start_date',
        'end_date',
        'time_limit',
        'available_days',
        'usage_limit',
        'limit_per_patient',
        'new_patients_only',
        'can_combine_with_other_promos',
        'terms_and_conditions',
        'internal_notes',
        'display_note',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_spend' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'time_limit' => 'datetime:H:i',
            'available_days' => 'array',
            'usage_limit' => 'integer',
            'limit_per_patient' => 'integer',
            'new_patients_only' => 'boolean',
            'can_combine_with_other_promos' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'promotion_service')
            ->withTimestamps();
    }

    public function treatmentPackages(): BelongsToMany
    {
        return $this->belongsToMany(TreatmentPackage::class, 'promotion_treatment_package')
            ->withTimestamps();
    }

    public function membershipPlans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class, 'membership_plan_promotion')
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_promotion')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (is_file(public_path($this->image))) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-lt text-green',
            'draft' => 'bg-yellow-lt text-yellow',
            'scheduled' => 'bg-blue-lt text-blue',
            'expired' => 'bg-red-lt text-red',
            'inactive' => 'bg-secondary-lt text-secondary',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getDiscountLabelAttribute(): string
    {
        return match ($this->discount_method) {
            'percentage' => rtrim(rtrim((string) $this->discount_value, '0'), '.').'%',
            'fixed' => '₱'.number_format((float) $this->discount_value, 2),
            'free_service' => 'Free Service',
            'bundle' => 'Bundle Promo',
            default => (string) $this->discount_value,
        };
    }

    public function getValidityLabelAttribute(): ?string
    {
        if (! $this->start_date && ! $this->end_date) {
            return null;
        }

        $start = $this->start_date?->format('Y-m-d');
        $end = $this->end_date?->format('Y-m-d');

        if ($start && $end) {
            return $start.' to '.$end;
        }

        return $start ?: $end;
    }

    public function getScopeLabelAttribute(): string
    {
        return match ($this->applies_to) {
            'services' => 'Services',
            'packages' => 'Treatment Packages',
            'memberships' => 'Membership Plans',
            'products' => 'Products',
            'all' => 'All',
            default => ucfirst(str_replace('_', ' ', $this->applies_to ?? 'general')),
        };
    }

    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->start_date && $today->lt($this->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($this->end_date && $today->gt($this->end_date->copy()->endOfDay())) {
            return false;
        }

        return true;
    }
}
