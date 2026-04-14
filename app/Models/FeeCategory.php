<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    protected $fillable = [
        'category',
        'age_range',
        'fee',
        'updated_by',
    ];

    protected $casts = [
        'fee' => 'integer',
    ];
}