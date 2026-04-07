<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_profiles', function (Blueprint $table) {
            // ── UUID Primary Key ───────────────────────────────────────────
            $table->uuid('id')->primary();

            // ── Identity Fields ────────────────────────────────────────────
            $table->string('first_name');
            $table->string('last_name');
            $table->string('contact_number')->nullable()->index();

            // ── Current Address (always up-to-date) ───────────────────────
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('place_of_origin')->nullable();

            // ── Soft Deletes + Timestamps ──────────────────────────────────
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_profiles');
    }
};
