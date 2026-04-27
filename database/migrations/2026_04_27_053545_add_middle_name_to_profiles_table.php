<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add middle_name to visitor_profiles if it doesn't exist
        if (!Schema::hasColumn('visitor_profiles', 'middle_name')) {
            Schema::table('visitor_profiles', function (Blueprint $table) {
                $table->string('middle_name', 255)->nullable()->after('first_name');
            });
        }

        // Add middle_name snapshot to visitor_visits if it doesn't exist
        if (!Schema::hasColumn('visitor_visits', 'snapshot_middle_name')) {
            Schema::table('visitor_visits', function (Blueprint $table) {
                $table->string('snapshot_middle_name', 255)->nullable()->after('snapshot_first_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('visitor_profiles', function (Blueprint $table) {
            $table->dropColumn('middle_name');
        });
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropColumn('snapshot_middle_name');
        });
    }
};