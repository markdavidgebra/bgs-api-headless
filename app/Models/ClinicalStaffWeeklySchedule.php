<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalStaffWeeklySchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClinicalStaffWeeklyScheduleFactory> */
    use HasFactory;

    protected $table = 'clinical_staff_weekly_schedules';

    protected $fillable = [
        'clinical_staff_id',
        'weekday',
        'is_active',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'clinical_staff_id' => 'integer',
            'weekday' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function clinicalStaff(): BelongsTo
    {
        return $this->belongsTo(ClinicalStaff::class, 'clinical_staff_id');
    }

    public function getDayLabelAttribute(): string
    {
        return match ($this->weekday) {
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            default => 'Day '.$this->weekday,
        };
    }

    public function getTimeSlotLabelAttribute(): string
    {
        if (! $this->is_active || ! $this->start_time || ! $this->end_time) {
            return '—';
        }

        $start = is_string($this->start_time)
            ? substr($this->start_time, 0, 5)
            : $this->start_time->format('H:i');
        $end = is_string($this->end_time)
            ? substr($this->end_time, 0, 5)
            : $this->end_time->format('H:i');

        return $start.' - '.$end;
    }
}
