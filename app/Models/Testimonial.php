<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonialFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'designation',
        'quote',
        'image',
        'sort_order',
        'status',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getImageUrlAttribute(): string
    {
        $fallback = asset('frontend/assets/images/testimonial/testimonial-4-1.jpg');

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
