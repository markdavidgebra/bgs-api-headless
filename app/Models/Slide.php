<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slide extends Model
{
    /** @use HasFactory<\Database\Factories\SlideFactory> */
    use HasFactory;

    protected $fillable = [
        'sort_order',
        'is_active',
        'subtitle',
        'title',
        'title_span',
        'description',
        'button_text',
        'button_url',
        'show_video',
        'video_url',
        'video_label',
        'image',
        'image_alt',
    ];

    // Surface the computed image URL in JSON responses (used by the Next.js public API).
    // Without this the accessor below only fires when accessed in PHP/Blade, leaving
    // the headless frontend to fall back to a raw path that misses the storage/ prefix
    // for uploaded files.
    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_video' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        $fallback = asset('frontend/assets/images/slider_f/slides.jpg');

        if (! $this->image) {
            return $fallback;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (is_file(public_path($this->image))) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }
}
