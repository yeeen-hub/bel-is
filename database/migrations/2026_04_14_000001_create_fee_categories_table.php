<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('age_range');
            $table->unsignedInteger('fee');
            $table->string('updated_by')->default('Admin');
            $table->timestamps();
        });

        // Seed default categories
        DB::table('fee_categories')->insert([
            ['category' => 'Child',          'age_range' => '0 – 12',  'fee' => 50,  'updated_by' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Adult',          'age_range' => '13 – 59', 'fee' => 100, 'updated_by' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Senior Citizen', 'age_range' => '60+',     'fee' => 30,  'updated_by' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};