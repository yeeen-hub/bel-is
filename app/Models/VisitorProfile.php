<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VisitorProfile extends Model
{
    use HasFactory, SoftDeletes;

    // ── UUID Primary Key ──────────────────────────────────────────────────────
    protected $keyType  = 'string';
    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'contact_number',
        'municipality',
        'province',
        'place_of_origin',
    ];

    // ── Full name accessor ────────────────────────────────────────────────────
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ── A profile can have many visits ────────────────────────────────────────
    public function visits()
    {
        return $this->hasMany(VisitorVisit::class, 'profile_id');
    }

    // ── Most recent visit ─────────────────────────────────────────────────────
    public function latestVisit()
    {
        return $this->hasOne(VisitorVisit::class, 'profile_id')
                    ->latestOfMany('arrival_at');
    }
}
