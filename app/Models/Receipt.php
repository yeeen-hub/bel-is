<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Receipt extends Model
{
    // Receipts use UUID primary keys (same as VisitorVisit).
    // HasUuids automatically generates a UUID for 'id' on create
    // so you never need to pass 'id' manually to Receipt::create().
    use HasUuids;

    protected $fillable = [
        'receipt_number',
        'visit_id',
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
        'member_breakdown',
        'synced_at',
    ];

    protected $casts = [
        'collected_at'     => 'datetime',
        'synced_at'        => 'datetime',
        'amount'           => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'member_breakdown' => 'array',
    ];

    public function visit()
    {
        return $this->belongsTo(VisitorVisit::class, 'visit_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}