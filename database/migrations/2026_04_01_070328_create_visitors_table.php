<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('registration_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('municipality');
            $table->string('province');
            $table->string('place_of_origin');
            $table->enum('purpose', ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']);
            $table->string('duration_of_stay');
            $table->string('contact_number')->nullable();
            $table->enum('fee_status', ['Collected', 'Waived', 'Pending'])->default('Pending');
            $table->timestamp('arrival_at')->useCurrent();
            $table->timestamp('departure_at')->nullable();
            $table->foreignId('registered_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes(); // ✅ Add this — required by FR-4.1.10
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};