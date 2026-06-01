<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No-op: these columns are already provided by Testbench's Fortify migration.
        // Keeping this file prevents migration filename collisions while avoiding duplicate columns.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op. See up().
    }
};
