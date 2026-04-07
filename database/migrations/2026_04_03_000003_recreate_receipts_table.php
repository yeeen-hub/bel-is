<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old receipts table (references old visitors integer ID)
        Schema::dropIfExists('receipts');

        Schema::create('receipts', function (Blueprint $table) {
            // ── UUID Primary Key ───────────────────────────────────────────
            $table->uuid('id')->primary();

            // ── Receipt Identifier ─────────────────────────────────────────
            $table->string('receipt_number')->unique();

            // ── Link to Visit (not profile) ────────────────────────────────
            // Each receipt belongs to a specific visit
            $table->uuid('visit_id');
            $table->foreign('visit_id')
                  ->references('id')
                  ->on('visitor_visits')
                  ->onDelete('cascade');

            // ── Fee Details ────────────────────────────────────────────────
            $table->decimal('amount', 10, 2)->default(100.00);
            $table->string('currency')->default('PHP');
            $table->enum('fee_type', ['Standard', 'Group', 'Waived'])->default('Standard');
            $table->integer('number_of_visitors')->default(1);
            $table->decimal('total_amount', 10, 2)->default(100.00);

            // ── Waiver Reason (required if Waived) ────────────────────────
            $table->string('waiver_reason')->nullable();

            // ── Payment ────────────────────────────────────────────────────
            $table->enum('payment_method', ['Cash'])->default('Cash');
            $table->unsignedBigInteger('collected_by');
            $table->foreign('collected_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->timestamp('collected_at');
            $table->text('notes')->nullable();

            // ── Sync fields ────────────────────────────────────────────────
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
