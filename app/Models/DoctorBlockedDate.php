<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorBlockedDate extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorBlockedDateFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'blocked_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'doctor_id' => 'integer',
            'blocked_date' => 'date',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
