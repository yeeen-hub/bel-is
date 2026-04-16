<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('require_strong_password')->default(true);
            $table->integer('max_login_attempts')->default(3);
            $table->integer('lockout_duration')->default(15); // in minutes
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('security_settings')->insert([
            'two_factor_enabled' => false,
            'require_strong_password' => true,
            'max_login_attempts' => 3,
            'lockout_duration' => 15,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};
