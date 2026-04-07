<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // User has registered many visitors
    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'registered_by');
    }

    // User has collected many receipts
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'collected_by');
    }

    // User has many audit logs
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // User has created many content
    public function tourismContents()
    {
        return $this->hasMany(TourismContent::class, 'created_by');
    }
}