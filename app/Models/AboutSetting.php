<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSetting extends Model
{
    protected $table = 'about_settings';

    protected $fillable = [
        'title', 'subtitle',
        'feature1_title', 'feature1_desc',
        'feature2_title', 'feature2_desc',
        'feature3_title', 'feature3_desc',
    ];

    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'title'          => 'What is Bel-is?',
            'subtitle'       => 'About Us',
            'feature1_title' => 'Our History',
            'feature1_desc'  => 'Experience the beautiful resorts of Bel-is.',
            'feature2_title' => 'Culture & Traditions',
            'feature2_desc'  => 'Immerse yourself in Bel-is\' local traditions.',
            'feature3_title' => 'Nature & Environment',
            'feature3_desc'  => 'Discover pristine beaches and lush landscapes.',
        ]);
    }
}