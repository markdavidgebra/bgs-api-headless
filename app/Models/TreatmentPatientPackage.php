<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentPatientPackage extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentPatientPackageFactory> */
    use HasFactory;

    protected $table = 'treatment_patient_package';

    protected $fillable = [
        'patient_id',
        'treatment_package_id',
        'purchased_at',
        'start_date',
        'end_date',
        'status',
        'total_sessions',
        'used_sessions',
        'remaining_sessions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_sessions' => 'integer',
            'used_sessions' => 'integer',
            'remaining_sessions' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function treatmentPackage(): BelongsTo
    {
        return $this->belongsTo(TreatmentPackage::class, 'treatment_package_id');
    }

    public function usageHistories(): HasMany
    {
        return $this->hasMany(TreatmentPackageUsageHistory::class, 'patient_package_id');
    }
}
