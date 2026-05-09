<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class About extends Model
{
    /** @use HasFactory<\Database\Factories\AboutFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'image',
        'meta',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getImageUrlAttribute(): string
    {
        $fallback = asset('frontend/assets/images/resources/about-one-img-1.jpg');

        if (! $this->image) {
            return $fallback;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (is_file(public_path($this->image))) {
            return asset($this->image);
        }

        return $fallback;
    }
}
