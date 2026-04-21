<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attraction extends Model
{
    protected $table = 'attractions';

    protected $fillable = [
        'name',
        'location',
        'description',
        'image',
        'sort_order',
    ];

    /**
     * Full public URL for the attraction image (or null if not set).
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }
}