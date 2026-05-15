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
        'discount_method',
        'discount_value',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
        ];
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
}
