<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            // Stores JSON array of per-member fee breakdown for group payments.
            // Each element: { visit_id, registration_id, full_name, visitor_category, fee }
            $table->json('member_breakdown')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('member_breakdown');
        });
    }
};