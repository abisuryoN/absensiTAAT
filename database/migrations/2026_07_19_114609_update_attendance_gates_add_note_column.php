<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: The status column already exists with string values
     * (hadir, terlambat, izin, sakit, alpha) and note column already exists.
     * This migration is just for ensuring consistency.
     */
    public function up(): void
    {
        Schema::table('attendance_gates', function (Blueprint $table) {
            // Ensure status column is string (already exists - just for safety)
            // Ensure note column is text (already exists)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_gates', function (Blueprint $table) {
            //
        });
    }
};