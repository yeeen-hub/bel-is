<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_visits', function (Blueprint $table) {
            // ── UUID Primary Key ───────────────────────────────────────────
            $table->uuid('id')->primary();

            // ── Reference Code (e.g. BEL-123456) ──────────────────────────
            // Used for public pre-registration handshake (Phase 2 Step 4)
            $table->string('reference_code')->unique()->nullable();

            // ── Link to Master Profile ─────────────────────────────────────
            // Nullable: pre-registrations exist before profile resolution
            $table->uuid('profile_id')->nullable()->index();
            $table->foreign('profile_id')
                  ->references('id')
                  ->on('visitor_profiles')
                  ->onDelete('set null');

            // ── Visit-Specific Fields ──────────────────────────────────────
            $table->string('registration_id')->unique()->nullable();
            $table->enum('purpose', ['Tourism', 'Research', 'Event', 'Official Visit', 'Other']);
            $table->string('duration_of_stay');
            $table->timestamp('arrival_at')->useCurrent();
            $table->timestamp('departure_at')->nullable();

            // ── Historical Snapshot ────────────────────────────────────────
            // These are COPIES of the profile address AT THE TIME OF VISIT.
            // They never change even if the profile is later updated.
            $table->string('snapshot_first_name')->nullable();
            $table->string('snapshot_last_name')->nullable();
            $table->string('snapshot_municipality')->nullable();
            $table->string('snapshot_province')->nullable();
            $table->string('snapshot_place_of_origin')->nullable();
            $table->string('snapshot_contact_number')->nullable();

            // ── Fee Status ─────────────────────────────────────────────────
            $table->enum('fee_status', ['Collected', 'Waived', 'Pending'])->default('Pending');

            // ── Waiver Reason (Phase 4 Step 8) ────────────────────────────
            // Required when fee_status = 'Waived'
            $table->string('waiver_reason')->nullable();

            // ── Registration Source ────────────────────────────────────────
            $table->enum('source', ['staff', 'pre_registration'])->default('staff');

            // ── Who registered this visit ──────────────────────────────────
            $table->unsignedBigInteger('registered_by')->nullable();
            $table->foreign('registered_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // ── Sync fields (Phase 5 Step 9) ──────────────────────────────
            $table->string('device_id')->nullable();   // identifies source device
            $table->timestamp('synced_at')->nullable(); // null = not yet synced to cloud

            // ── Soft Deletes + Timestamps ──────────────────────────────────
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_visits');
    }
};
