<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Receipt extends Model
{
    use HasFactory;

    // ── UUID Primary Key ──────────────────────────────────────────────────────
    protected $keyType   = 'string';
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
        'receipt_number',
        'visit_id',       // ← was visitor_id, now points to visitor_visits
        'amount',
        'currency',
        'fee_type',
        'number_of_visitors',
        'total_amount',
        'waiver_reason',
        'payment_method',
        'collected_by',
        'collected_at',
        'notes',
        'synced_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'total_amount' => 'decimal:2',
        'collected_at' => 'datetime',
        'synced_at'    => 'datetime',
    ];

    // ── Receipt belongs to a visit ────────────────────────────────────────────
    public function visit()
    {
        return $this->belongsTo(VisitorVisit::class, 'visit_id');
    }

    // ── Convenience: get the profile through the visit ────────────────────────
    public function profile()
    {
        return $this->hasOneThrough(
            VisitorProfile::class,
            VisitorVisit::class,
            'id',         // visitor_visits.id
            'id',         // visitor_profiles.id
            'visit_id',   // receipts.visit_id
            'profile_id'  // visitor_visits.profile_id
        );
    }

    // ── Receipt was collected by a user (staff) ───────────────────────────────
    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}