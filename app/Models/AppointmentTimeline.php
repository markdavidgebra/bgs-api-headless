<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentTimeline extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentTimelineFactory> */
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'event',
        'description',
        'event_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
