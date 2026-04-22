<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorDestination extends Model
{
    protected $fillable = ['visit_id', 'attraction_id', 'other_destination'];

    public function visit()
    {
        return $this->belongsTo(VisitorVisit::class, 'visit_id');
    }

    public function attraction()
    {
        return $this->belongsTo(BarangayAttraction::class, 'attraction_id');
    }
}