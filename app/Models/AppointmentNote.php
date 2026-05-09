<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentNote extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentNoteFactory> */
    use HasFactory;

    protected $table = 'appointment_notes';

    protected $fillable = [
        'appointment_id',
        'patient_concern',
        'appointment_remarks',
        'admin_notes',
        'doctor_notes',
        'instructions',
        'alerts',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
