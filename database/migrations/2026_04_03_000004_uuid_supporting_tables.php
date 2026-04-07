<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Recreate audit_logs with UUID and string target_id ─────────────
        Schema::dropIfExists('audit_logs');

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->string('action');
            $table->string('module');
            // target_id is now a string to support both UUID and integer IDs
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // ── Recreate tourism_contents with UUID ────────────────────────────
        Schema::dropIfExists('virtual_hotspots');
        Schema::dropIfExists('tourism_contents');

        Schema::create('tourism_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['attraction', 'package', 'circuit', 'history', 'virtual_scene']);
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });

        // ── Recreate virtual_hotspots with UUID ────────────────────────────
        Schema::create('virtual_hotspots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('scene_id');
            $table->foreign('scene_id')
                  ->references('id')
                  ->on('tourism_contents')
                  ->onDelete('cascade');
            $table->enum('type', ['info', 'scene_link', 'media']);
            $table->string('label');
            $table->decimal('pitch', 8, 5)->default(0);
            $table->decimal('yaw',   8, 5)->default(0);
            $table->uuid('target_scene_id')->nullable();
            $table->foreign('target_scene_id')
                  ->references('id')
                  ->on('tourism_contents')
                  ->onDelete('set null');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_hotspots');
        Schema::dropIfExists('tourism_contents');
        Schema::dropIfExists('audit_logs');
    }
};
