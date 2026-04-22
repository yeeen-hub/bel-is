<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VisitorVisit extends Model
{
    use HasFactory, SoftDeletes;

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
        'reference_code',
        'profile_id',
        'registration_id',
        'purpose',
        'purpose_other',
        'group_code',
        'duration_of_stay',
        'visitor_category',
        'arrival_at',
        'departure_at',
        // Historical snapshot
        'snapshot_first_name',
        'snapshot_last_name',
        'snapshot_municipality',
        'snapshot_province',
        'snapshot_place_of_origin',
        'snapshot_contact_number',
        // Fee
        'fee_status',
        'waiver_reason',
        // Meta
        'source',
        'registered_by',
        'device_id',
        'synced_at',
    ];

    protected $casts = [
        'arrival_at'   => 'datetime',
        'departure_at' => 'datetime',
        'synced_at'    => 'datetime',
    ];

    // ── Computed: full name from snapshot ─────────────────────────────────────
    public function getFullNameAttribute(): string
    {
        return "{$this->snapshot_first_name} {$this->snapshot_last_name}";
    }

    // ── Computed: place of origin from snapshot ───────────────────────────────
    public function getPlaceOfOriginAttribute(): string
    {
        return $this->snapshot_place_of_origin
            ?? "{$this->snapshot_municipality}, {$this->snapshot_province}";
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function profile()
    {
        return $this->belongsTo(VisitorProfile::class, 'profile_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'visit_id');
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Destinations this visitor selected at registration.
     * Each row = one attraction_id (named) or null + other_destination (free text).
     * Used by the area/sitio and attraction filters in reports.
     */
    public function destinations()
    {
        return $this->hasMany(VisitorDestination::class, 'visit_id');
    }

    // ── Snapshot: copies profile's current data into the visit record ─────────
    // Called once when a visit is created — preserves historical address.
    public function takeSnapshot(VisitorProfile $profile): void
    {
        $this->snapshot_first_name      = $profile->first_name;
        $this->snapshot_last_name       = $profile->last_name;
        $this->snapshot_municipality    = $profile->municipality;
        $this->snapshot_province        = $profile->province;
        $this->snapshot_place_of_origin = $profile->place_of_origin;
        $this->snapshot_contact_number  = $profile->contact_number;
    }

    // ── Scope: only staff-confirmed visits (visible in records/counts) ────────
    public function scopeConfirmed($query)
    {
        return $query->where('source', 'staff');
    }

    // ── Scope: unsynced records (for offline sync engine) ────────────────────
    public function scopeUnsynced($query)
    {
        return $query->whereNull('synced_at');
    }
}