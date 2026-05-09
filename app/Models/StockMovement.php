<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            default => ucfirst(str_replace('_', ' ', $this->type ?? 'unknown')),
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'in' => 'bg-green-lt text-green',
            'out' => 'bg-red-lt text-red',
            'adjustment' => 'bg-yellow-lt text-yellow',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getSignedQuantityAttribute(): string
    {
        $quantity = (int) $this->quantity;

        return match ($this->type) {
            'in' => '+'.$quantity,
            'out' => '-'.$quantity,
            default => (string) $quantity,
        };
    }
}
