<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentPackageUsageHistory extends Model
{
    /** @use HasFactory<\Database\Factories\TreatmentPackageUsageHistoryFactory> */
    use HasFactory;

    protected $table = 'treatment_package_usage_histories';

    protected $fillable = [
        'patient_package_id',
        'patient_id',
        'service_id',
        'used_on',
        'session_change',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'used_on' => 'date',
            'session_change' => 'integer',
        ];
    }

    public function patientPackage(): BelongsTo
    {
        return $this->belongsTo(TreatmentPatientPackage::class, 'patient_package_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
