<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangay_attractions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('General'); // Resort, Beach, Falls, Landmark, etc.
            $table->text('description')->nullable();
            $table->unsignedBigInteger('sitio_id')->nullable(); // FK to sitios
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('sitio_id')->references('id')->on('sitios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangay_attractions');
    }
};