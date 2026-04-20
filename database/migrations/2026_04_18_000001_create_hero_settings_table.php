<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tagline')->default('Discover the beauty of');
            $table->string('barangay')->default('Bel-is');
            $table->string('mun_prov')->default('Buruanga, Aklan');
            $table->string('sub')->default('Explore nature, culture, and hidden destinations');
            $table->string('background_image')->nullable(); // stores path like 'hero/bg.jpg'
            $table->timestamps();
        });

        // Seed one default row so there's always a record to update
        DB::table('hero_settings')->insert([
            'tagline'          => 'Discover the beauty of',
            'barangay'         => 'Bel-is',
            'mun_prov'         => 'Buruanga, Aklan',
            'sub'              => 'Explore nature, culture, and hidden destinations',
            'background_image' => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_settings');
    }
};