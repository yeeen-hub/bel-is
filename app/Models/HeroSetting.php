<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSetting extends Model
{
    protected $table = 'hero_settings';

    protected $fillable = [
        'tagline',
        'barangay',
        'mun_prov',
        'sub',
        'background_image',
    ];

    /**
     * Always returns the single settings row, creating it if missing.
     */
    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'tagline'          => 'Discover the beauty of',
            'barangay'         => 'Bel-is',
            'mun_prov'         => 'Buruanga, Aklan',
            'sub'              => 'Explore nature, culture, and hidden destinations',
            'background_image' => null,
        ]);
    }

    /**
     * Full public URL for the background image (or null if not set).
     */
    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->background_image
            ? asset('storage/' . $this->background_image)
            : null;
    }
}