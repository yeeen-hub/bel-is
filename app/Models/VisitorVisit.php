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
        'id',
        'registration_id',
        'reference_code',
        'profile_id',
        'group_code',
        'source',
        'registered_by',
        'purpose',
        'purpose_other',
        'duration_of_stay',
        'is_day_tour',
        'nights',
        'visitor_category',
        'fee_status',
        'waiver_reason',
        'arrival_at',
        // ── Tourist Arrival Form fields ───────────────────────────────────────
        'sex',
        'age',
        'nationality',
        'town_city',
        'country',
        'remarks',
        // ── Snapshot fields ───────────────────────────────────────────────────
        'snapshot_first_name',
        'snapshot_middle_name',
        'snapshot_last_name',
        'snapshot_municipality',
        'snapshot_province',
        'snapshot_place_of_origin',
        'snapshot_contact_number',
    ];

    protected $casts = [
        'arrival_at'   => 'datetime',
        'departure_at' => 'datetime',
        'synced_at'    => 'datetime',
        'is_day_tour'  => 'boolean',
        'age'          => 'integer',
    ];

    // ── Computed: full name from snapshot ─────────────────────────────────────
    public function getFullNameAttribute(): string
    {
        return trim("{$this->snapshot_first_name} {$this->snapshot_last_name}");
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

    public function destinations()
    {
        return $this->hasMany(VisitorDestination::class, 'visit_id');
    }

    // ── Snapshot ──────────────────────────────────────────────────────────────
    // Called once at visit creation — preserves historical profile data.
    public function takeSnapshot(VisitorProfile $profile): void
    {
        $this->snapshot_first_name      = $profile->first_name;
        $this->snapshot_middle_name     = $profile->middle_name;   // ← was missing
        $this->snapshot_last_name       = $profile->last_name;
        $this->snapshot_municipality    = $profile->municipality;
        $this->snapshot_province        = $profile->province;
        $this->snapshot_place_of_origin = $profile->place_of_origin;
        $this->snapshot_contact_number  = $profile->contact_number;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeConfirmed($query)
    {
        return $query->where('source', 'staff');
    }

    public function scopeUnsynced($query)
    {
        return $query->whereNull('synced_at');
    }
}