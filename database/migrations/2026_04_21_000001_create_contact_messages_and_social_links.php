<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Visitor messages inbox
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Add social link columns to existing contact_settings
        Schema::table('contact_settings', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('phone_hours');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('twitter_url')->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::table('contact_settings', function (Blueprint $table) {
            $table->dropColumn(['facebook_url', 'instagram_url', 'twitter_url']);
        });
    }
};