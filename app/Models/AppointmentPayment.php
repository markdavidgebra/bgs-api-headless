<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentPayment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'invoice_no',
        'amount',
        'payment_method',
        'payment_status',
        'is_paid',
        'deposit_notes',
        'reference_no',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
