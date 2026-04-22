<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnrecognizedAttraction extends Model
{
    protected $fillable = ['visit_id', 'name', 'is_reviewed', 'reviewed_at'];

    protected $casts = [
        'is_reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function visit()
    {
        return $this->belongsTo(VisitorVisit::class, 'visit_id');
    }
}