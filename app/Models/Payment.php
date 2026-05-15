<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'payment_id',
        'patient_id',
        'reference_type',
        'reference_id',
        'amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'transaction_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'patient_id' => 'integer',
            'reference_id' => 'integer',
            'amount' => 'decimal:2',
            'payment_date' => 'date',
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

    public function referenceAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'reference_id');
    }

    public function referencePackage(): BelongsTo
    {
        return $this->belongsTo(TreatmentPackage::class, 'reference_id');
    }

    public function referenceMembership(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'reference_id');
    }

    public function referenceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reference_id');
    }

    public function referenceService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'reference_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-green-lt text-green',
            'unpaid' => 'bg-red-lt text-red',
            'partial' => 'bg-yellow-lt text-yellow',
            'refunded' => 'bg-blue-lt text-blue',
            'cancelled' => 'bg-secondary-lt text-secondary',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst(str_replace('_', ' ', $this->payment_method ?? 'unknown')),
        };
    }

    public function getReferenceTypeLabelAttribute(): string
    {
        return match ($this->reference_type) {
            'appointment' => 'Appointment',
            'service' => 'Service',
            'package' => 'Package',
            'membership' => 'Membership',
            'product' => 'Product',
            default => ucfirst(str_replace('_', ' ', $this->reference_type ?? 'record')),
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₱'.number_format((float) $this->amount, 2);
    }

    public function getReferenceNameAttribute(): ?string
    {
        return match ($this->reference_type) {
            'appointment' => $this->appointment_reference_name ?? $this->legacy_pos_service_reference_name,
            'service' => $this->service_reference_name,
            'package' => $this->package_reference_name,
            'membership' => $this->membership_reference_name,
            'product' => $this->product_reference_name,
            default => null,
        };
    }

    public function getAppointmentReferenceNameAttribute(): ?string
    {
        if ($this->reference_type !== 'appointment' || ! $this->reference_id) {
            return null;
        }

        $appointment = $this->referenceAppointment;

        if (! $appointment) {
            return null;
        }

        return $appointment->service?->name
            ?? $appointment->appointment_no
            ?? null;
    }

    public function getPackageReferenceNameAttribute(): ?string
    {
        if ($this->reference_type !== 'package' || ! $this->reference_id) {
            return null;
        }

        return $this->referencePackage?->name;
    }

    public function getMembershipReferenceNameAttribute(): ?string
    {
        if ($this->reference_type !== 'membership' || ! $this->reference_id) {
            return null;
        }

        return $this->referenceMembership?->name;
    }

    public function getProductReferenceNameAttribute(): ?string
    {
        if ($this->reference_type !== 'product' || ! $this->reference_id) {
            return null;
        }

        return $this->referenceProduct?->name;
    }

    public function getServiceReferenceNameAttribute(): ?string
    {
        if ($this->reference_type !== 'service' || ! $this->reference_id) {
            return null;
        }

        return $this->referenceService?->name;
    }

    /**
     * Older POS checkouts stored walk-in services as appointment + service id.
     */
    public function getLegacyPosServiceReferenceNameAttribute(): ?string
    {
        if ($this->reference_type !== 'appointment' || ! $this->reference_id || $this->referenceAppointment) {
            return null;
        }

        return $this->referenceService?->name;
    }

    public function getAssignedDoctorNameAttribute(): ?string
    {
        if ($this->reference_type !== 'appointment' || ! $this->reference_id) {
            return null;
        }

        return $this->referenceAppointment?->doctor?->name;
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->payment_id ?? 'P', 0, 1));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function generatePaymentId(): string
    {
        $latest = static::latest('id')->first();
        $nextNumber = ($latest?->id ?? 0) + 1;

        return 'PAY-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
