<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_settings', function (Blueprint $table) {
            $table->renameColumn('first_phrase', 'tagline');
            $table->renameColumn('second_phrase', 'barangay');
            $table->renameColumn('third_phrase', 'mun_prov');
            $table->renameColumn('fourth_phrase', 'sub');
        });
    }

    public function down(): void
    {
        Schema::table('hero_settings', function (Blueprint $table) {
            $table->renameColumn('tagline',  'first_phrase');
            $table->renameColumn('barangay', 'second_phrase');
            $table->renameColumn('mun_prov', 'third_phrase');
            $table->renameColumn('sub',      'fourth_phrase');
        });
    }
};