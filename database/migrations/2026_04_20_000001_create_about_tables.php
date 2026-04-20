<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('What is Bel-is?');
            $table->string('subtitle')->default('About Us');
            $table->string('feature1_title')->default('Our History');
            $table->text('feature1_desc');                          // ← no default
            $table->string('feature2_title')->default('Culture & Traditions');
            $table->text('feature2_desc');                          // ← no default
            $table->string('feature3_title')->default('Nature & Environment');
            $table->text('feature3_desc');                          // ← no default
            $table->timestamps();
        });

        DB::table('about_settings')->insert([
            'title'          => 'What is Bel-is?',
            'subtitle'       => 'About Us',
            'feature1_title' => 'Our History',
            'feature1_desc'  => 'Experience the beautiful resorts of Bel-is with breathtaking views, serene ambiance, and world-class amenities.',
            'feature2_title' => 'Culture & Traditions',
            'feature2_desc'  => 'Immerse yourself in Bel-is\' local traditions, festivals, and culinary delights. Connect with the community and make unforgettable memories.',
            'feature3_title' => 'Nature & Environment',
            'feature3_desc'  => 'Discover pristine beaches, lush landscapes, and the rich biodiversity that makes Bel-is a must-visit destination in Aklan.',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        Schema::create('about_images', function (Blueprint $table) {
            $table->id();
            $table->string('image');           // path like 'about/photo.jpg'
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_images');
        Schema::dropIfExists('about_settings');
    }
};