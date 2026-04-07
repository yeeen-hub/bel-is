<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VirtualHotspot extends Model
{
    use HasFactory;

    protected $fillable = [
        'scene_id',
        'type',
        'label',
        'pitch',
        'yaw',
        'target_scene_id',
        'content',
        'media_url',
        'is_active',
    ];

    protected $casts = [
        'pitch'     => 'float',
        'yaw'       => 'float',
        'is_active' => 'boolean',
    ];

    // Hotspot belongs to a scene
    public function scene()
    {
        return $this->belongsTo(TourismContent::class, 'scene_id');
    }

    // Hotspot can link to another scene (self-referential)
    public function targetScene()
    {
        return $this->belongsTo(TourismContent::class, 'target_scene_id');
    }
}