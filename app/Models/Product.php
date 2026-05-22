<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand',
        'sku',
        'description',
        'showcase_assurance_lines',
        'image',
        'cost_price',
        'selling_price',
        'discount_price',
        'stock_quantity',
        'minimum_stock_alert',
        'unit',
        'status',
        'is_available_for_sale',
        'expiry_date',
        'batch_number',
        'supplier',
    ];

    /**
     * Computed attributes exposed on JSON responses for the admin UI.
     *
     * @var list<string>
     */
    protected $appends = [
        'image_url',
        'stock_status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_stock_alert' => 'integer',
            'is_available_for_sale' => 'boolean',
            'expiry_date' => 'date',
            'showcase_assurance_lines' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function categoryItem(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function prescribedOnAppointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_product')
            ->withPivot(['quantity'])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Derived Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Bullets under the description on the public product detail page.
     * When the column is null (never set), keep the original site defaults.
     *
     * @return list<string>
     */
    public function resolvedShowcaseAssuranceLines(): array
    {
        if ($this->showcase_assurance_lines === null) {
            return [
                'Curated for post-treatment compatibility',
                'Authentic clinic-sourced inventory',
            ];
        }

        $raw = $this->showcase_assurance_lines;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $line) {
            $t = is_string($line) ? trim($line) : '';
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! filled($this->image)) {
            return null;
        }

        $raw = trim((string) $this->image);

        if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
            return $raw;
        }

        $normalized = str_replace('\\', '/', $raw);
        $normalized = ltrim($normalized, '/');

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, strlen('public/'));
        }

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, 'storage/')) {
            $relative = substr($normalized, strlen('storage/'));

            return asset('storage/'.$relative);
        }

        if (is_file(public_path($normalized))) {
            return asset($normalized);
        }

        if (is_file(storage_path('app/public/'.$normalized))) {
            return asset('storage/'.$normalized);
        }

        // Admin uploads use public/products/...; avoid wrongly prefixing storage/ when the file check fails (e.g. path quirks).
        return asset($normalized);
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->categoryItem?->name;
    }

    public function getFinalPriceAttribute(): string
    {
        $price = $this->discount_price ?? $this->selling_price ?? 0;

        return number_format((float) $price, 2, '.', '');
    }

    public function getProfitAmountAttribute(): float
    {
        return max(
            (float) ($this->selling_price ?? 0) - (float) ($this->cost_price ?? 0),
            0
        );
    }

    public function getStockStatusAttribute(): string
    {
        if ((int) $this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ((int) $this->stock_quantity <= (int) $this->minimum_stock_alert) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusBadgeAttribute(): string
    {
        return match ($this->stock_status) {
            'in_stock' => 'bg-green-lt text-green',
            'low_stock' => 'bg-yellow-lt text-yellow',
            'out_of_stock' => 'bg-red-lt text-red',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-lt text-green',
            'inactive' => 'bg-secondary-lt text-secondary',
            'archived' => 'bg-red-lt text-red',
            default => 'bg-blue-lt text-blue',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}
