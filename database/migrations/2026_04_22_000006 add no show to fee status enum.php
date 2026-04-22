<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MariaDB/MySQL: modify the enum to add 'No Show'
        DB::statement("ALTER TABLE visitor_visits MODIFY COLUMN fee_status ENUM('Collected','Waived','Pending','No Show') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        // Revert — any 'No Show' rows will need manual cleanup first
        DB::statement("ALTER TABLE visitor_visits MODIFY COLUMN fee_status ENUM('Collected','Waived','Pending') NOT NULL DEFAULT 'Pending'");
    }
};