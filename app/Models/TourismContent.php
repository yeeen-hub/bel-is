<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TourismContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'body',
        'excerpt',
        'cover_image',
        'gallery',
        'meta',
        'is_published',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'gallery'      => 'array',
        'meta'         => 'array',
        'is_published' => 'boolean',
    ];

    // Content was created by a user (admin)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Content (scene) has many hotspots
    public function hotspots()
    {
        return $this->hasMany(VirtualHotspot::class, 'scene_id');
    }
}