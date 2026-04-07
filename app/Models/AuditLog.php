<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    // ── UUID Primary Key ──────────────────────────────────────────────────────
    protected $keyType   = 'string';
    public $incrementing = false;
    public $timestamps   = false; // only has created_at

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
        'user_id',
        'action',
        'module',
        'target_type',
        'target_id',   
        'old_values',
        'new_values',
        'ip_address',
        'device_id',
    ];

    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'created_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}