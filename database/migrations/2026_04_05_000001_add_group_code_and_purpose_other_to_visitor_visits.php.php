<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {

            // purpose_other: free-text when purpose = 'Other'
            // Add only if it doesn't already exist (safe to run even if
            // the previous migration already added it)
            if (!Schema::hasColumn('visitor_visits', 'purpose_other')) {
                $table->string('purpose_other')->nullable()->after('purpose');
            }

            // group_code: stores the group leader's reference_code for every
            // member of a group pre-registration (including the leader).
            // Allows staff to look up the full group from any one member's code.
            // NULL = individual registration (not part of a group).
            if (!Schema::hasColumn('visitor_visits', 'group_code')) {
                $table->string('group_code')->nullable()->after('purpose_other');
                $table->index('group_code'); // fast lookup by group
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            if (Schema::hasColumn('visitor_visits', 'group_code')) {
                $table->dropIndex(['group_code']);
                $table->dropColumn('group_code');
            }
            if (Schema::hasColumn('visitor_visits', 'purpose_other')) {
                $table->dropColumn('purpose_other');
            }
        });
    }
};
