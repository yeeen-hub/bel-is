<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_id',
        'first_name',
        'last_name',
        'municipality',
        'province',
        'place_of_origin',
        'purpose',
        'duration_of_stay',
        'contact_number',
        'fee_status',
        'arrival_at',
        'departure_at',
        'registered_by',
    ];

    protected $casts = [
        'arrival_at'   => 'datetime',
        'departure_at' => 'datetime',
    ];

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Visitor was registered by a user (staff)
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // Visitor has one receipt
    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}