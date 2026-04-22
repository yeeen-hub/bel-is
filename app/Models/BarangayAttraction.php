<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangayAttraction extends Model
{
    protected $fillable = ['name', 'type', 'description', 'sitio_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function sitio()
    {
        return $this->belongsTo(Sitio::class, 'sitio_id');
    }

    public function visitorDestinations()
    {
        return $this->hasMany(VisitorDestination::class, 'attraction_id');
    }
}