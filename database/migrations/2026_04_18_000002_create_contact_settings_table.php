<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('email')->default('help@info.com');
            $table->string('phone')->default('+63 123 456 7890');
            $table->string('email_hours')->default('Monday – Friday 6 am to 8 pm');
            $table->string('phone_hours')->default('Monday – Friday 6 am to 8 pm');
            $table->timestamps();
        });

        // Seed one default row
        DB::table('contact_settings')->insert([
            'email'       => 'help@info.com',
            'phone'       => '+63 123 456 7890',
            'email_hours' => 'Monday – Friday 6 am to 8 pm',
            'phone_hours' => 'Monday – Friday 6 am to 8 pm',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};