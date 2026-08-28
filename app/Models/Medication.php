<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    protected $table = 'medications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'generic_name',
        'strength',
        'form',
        'route',
        'notes',
        'is_controlled',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_controlled' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Display label, e.g. "Biogesic 500 mg (Paracetamol)".
     */
    public function getLabelAttribute(): string
    {
        $label = trim((string) $this->name);

        if (filled($this->strength)) {
            $label = trim($label.' '.$this->strength);
        }

        if (filled($this->generic_name) && strcasecmp((string) $this->generic_name, (string) $this->name) !== 0) {
            $label .= ' ('.$this->generic_name.')';
        }

        return $label;
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'medication_id');
    }
}
