<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained('tourism_contents')->onDelete('cascade');
            $table->enum('type', ['info', 'scene_link', 'media']);
            $table->string('label');
            $table->decimal('pitch', 8, 5)->default(0);
            $table->decimal('yaw', 8, 5)->default(0);
            $table->foreignId('target_scene_id')->nullable()->constrained('tourism_contents')->onDelete('set null');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_hotspots');
    }
};