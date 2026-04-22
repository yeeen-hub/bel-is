<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sitio extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function barangayAttractions()
    {
        return $this->hasMany(BarangayAttraction::class, 'sitio_id');
    }
}