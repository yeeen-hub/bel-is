<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table linking a visitor_visit to one or more barangay_attractions.
        // When the visitor selects "Other", attraction_id is NULL and other_destination holds the text.
        Schema::create('visitor_destinations', function (Blueprint $table) {
            $table->id();
            $table->char('visit_id', 36);
            $table->unsignedBigInteger('attraction_id')->nullable(); // NULL when "Other"
            $table->string('other_destination')->nullable();         // filled when "Other"
            $table->timestamps();

            $table->foreign('visit_id')->references('id')->on('visitor_visits')->onDelete('cascade');
            $table->foreign('attraction_id')->references('id')->on('barangay_attractions')->onDelete('cascade');

            // A visit can only be linked to the same attraction once
            $table->unique(['visit_id', 'attraction_id'], 'vd_visit_attraction_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_destinations');
    }
};