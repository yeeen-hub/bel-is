<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stores "other" destination entries so the Attraction Management page
        // can show a notification about potentially new/undiscovered attractions.
        Schema::create('unrecognized_attractions', function (Blueprint $table) {
            $table->id();
            $table->char('visit_id', 36);                    // which visit reported it
            $table->string('name');                          // what the visitor typed
            $table->boolean('is_reviewed')->default(false);  // staff acknowledged it
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('visit_id')->references('id')->on('visitor_visits')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unrecognized_attractions');
    }
};