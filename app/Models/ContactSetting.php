<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $table = 'contact_settings';

    protected $fillable = [
        'email',
        'phone',
        'email_hours',
        'phone_hours',
        'facebook_url',
        'instagram_url',
        'twitter_url',
    ];

    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'email'         => 'help@info.com',
            'phone'         => '+63 123 456 7890',
            'email_hours'   => 'Monday – Friday 6 am to 8 pm',
            'phone_hours'   => 'Monday – Friday 6 am to 8 pm',
            'facebook_url'  => null,
            'instagram_url' => null,
            'twitter_url'   => null,
        ]);
    }
}