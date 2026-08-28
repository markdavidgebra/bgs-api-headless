<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalStaffBlockedDate extends Model
{
    /** @use HasFactory<\Database\Factories\ClinicalStaffBlockedDateFactory> */
    use HasFactory;

    protected $table = 'clinical_staff_blocked_dates';

    protected $fillable = [
        'clinical_staff_id',
        'blocked_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'clinical_staff_id' => 'integer',
            'blocked_date' => 'date',
        ];
    }

    public function clinicalStaff(): BelongsTo
    {
        return $this->belongsTo(ClinicalStaff::class, 'clinical_staff_id');
    }
}
