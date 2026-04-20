<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            // Stores the fee category label (e.g. "Regular", "Senior Citizen", "Child")
            // Nullable so existing rows are unaffected.
            $table->string('visitor_category')->nullable()->after('duration_of_stay');
        });
    }

    public function down(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropColumn('visitor_category');
        });
    }
};