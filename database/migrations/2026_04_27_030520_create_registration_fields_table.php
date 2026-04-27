<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            // New fields from Tourist Arrival Form redesign
            $table->string('sex', 10)->nullable()->after('visitor_category');        // M / F
            $table->unsignedTinyInteger('age')->nullable()->after('sex');            // raw age — category auto-derived
            $table->string('nationality', 20)->nullable()->after('age');             // Local / Aklanon / OFW / Foreign
            $table->string('town_city', 255)->nullable()->after('nationality');      // replaces municipality for new form
            $table->string('country', 255)->nullable()->after('town_city');          // blank = Philippines
            $table->text('remarks')->nullable()->after('country');                   // Complaints/Concerns/Suggestions
            $table->boolean('is_day_tour')->default(true)->after('duration_of_stay'); // Day Tour toggle
            $table->unsignedTinyInteger('nights')->nullable()->after('is_day_tour'); // No. of Nights if not day tour
        });

        // Form field settings — stores required/optional state per registration field
        Schema::create('form_field_settings', function (Blueprint $table) {
            $table->id();
            $table->string('field_key', 100)->unique(); // e.g. 'contact_number', 'remarks'
            $table->string('label', 255);               // display name shown in settings UI
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('visitor_visits', function (Blueprint $table) {
            $table->dropColumn(['sex', 'age', 'nationality', 'town_city', 'country', 'remarks', 'is_day_tour', 'nights']);
        });
        Schema::dropIfExists('form_field_settings');
    }
};