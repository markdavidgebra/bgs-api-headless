<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    protected $table = 'prescription_items';

    /**
     * medication_name / strength / form / route are snapshots taken when the
     * prescription was written; they are intentionally not re-read from medications.
     *
     * @var list<string>
     */
    protected $fillable = [
        'prescription_id',
        'medication_id',
        'medication_name',
        'strength',
        'form',
        'route',
        'dosage',
        'frequency',
        'duration',
        'quantity',
        'instructions',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class, 'medication_id');
    }
}
