<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TreatmentPackage extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentPackageFactory> */
    use HasFactory;

    protected $table = 'treatment_packages';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'image',
        'status',
        'price',
        'original_price',
        'discount_percent',
        'validity_value',
        'validity_type',
        'expiry_rule',
        'max_usage_per_day',
        'allow_sharing',
        'is_refundable',
        'before_care',
        'aftercare',
        'internal_notes',
    ];

    /**
     * Computed attributes exposed on JSON responses for the admin UI.
     *
     * @var list<string>
     */
    protected $appends = [
        'image_url',
        'validity_label',
        'total_price',
        'validity_duration',
        'validity_unit',
        'refundable',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'validity_value' => 'integer',
            'max_usage_per_day' => 'integer',
            'allow_sharing' => 'boolean',
            'is_refundable' => 'boolean',
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
            'treatment_service_package',
            'treatment_package_id',
            'service_id'
        )->withPivot('sessions')->withTimestamps();
    }

    public function clinicalStaff(): BelongsToMany
    {
        return $this->belongsToMany(
            ClinicalStaff::class,
            'treatment_clinical_staff_package',
            'treatment_package_id',
            'clinical_staff_id'
        )->withTimestamps();
    }

    public function patientPackages(): HasMany
    {
        return $this->hasMany(TreatmentPatientPackage::class, 'treatment_package_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Listed / bundle price.
     * Form field total_price maps to column price.
     */
    public function getTotalPriceAttribute(): ?string
    {
        return $this->price !== null ? (string) $this->price : null;
    }

    public function getValidityDurationAttribute(): ?int
    {
        return $this->validity_value;
    }

    public function getValidityUnitAttribute(): ?string
    {
        return $this->validity_type;
    }

    public function getRefundableAttribute(): bool
    {
        return (bool) $this->is_refundable;
    }

    public function getNotesAttribute(): ?string
    {
        return $this->internal_notes;
    }

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

    public function getTotalSessionsAttribute(): int
    {
        return (int) $this->services->sum(function ($service) {
            return (int) ($service->pivot->sessions ?? 0);
        });
    }

    public function getDiscountAmountAttribute(): float
    {
        if ($this->original_price && $this->price) {
            return max((float) $this->original_price - (float) $this->price, 0);
        }

        return 0;
    }

    public function getValidityLabelAttribute(): ?string
    {
        if (! $this->validity_value || ! $this->validity_type) {
            return null;
        }

        return $this->validity_value.' '.Str::plural($this->validity_type, $this->validity_value);
    }
}
