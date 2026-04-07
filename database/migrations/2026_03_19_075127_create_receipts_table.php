<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->default(100.00);
            $table->string('currency')->default('PHP');
            $table->enum('fee_type', ['Standard', 'Group', 'Waived'])->default('Standard');
            $table->integer('number_of_visitors')->default(1);
            $table->decimal('total_amount', 10, 2)->default(100.00);
            $table->enum('payment_method', ['Cash'])->default('Cash');
            $table->foreignId('collected_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('collected_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};