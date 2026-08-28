<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $table = 'prescriptions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'prescription_no',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'issued_at',
        'diagnosis',
        'notes',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $prescription): void {
            if (! filled($prescription->prescription_no)) {
                $prescription->prescription_no = self::generatePrescriptionNo();
            }

            if ($prescription->issued_at === null) {
                $prescription->issued_at = now();
            }
        });
    }

    /**
     * Sequential per-year reference, mirroring how Appointment::appointment_no is built.
     */
    public static function generatePrescriptionNo(): string
    {
        $prefix = 'RX-'.now()->format('Y').'-';

        $last = self::query()
            ->where('prescription_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('prescription_no');

        $lastSeq = 0;
        if (is_string($last) && str_starts_with($last, $prefix)) {
            $lastSeq = (int) substr($last, strlen($prefix));
        }

        return $prefix.str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
