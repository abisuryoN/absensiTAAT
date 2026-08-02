<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace 'alpha' with 'tidak_hadir' in ENUM columns.
     *
     * MySQL does not support removing ENUM values directly via Schema builder,
     * so we use raw SQL to modify the ENUM.
     */
    public function up(): void
    {
        // SQLite (used in tests) does not support MODIFY COLUMN or ENUM types.
        // The initial schema already creates the correct column type for SQLite.
        if (DB::getDriverName() === 'sqlite') {
            // Just data-fix: update any stale 'alpha' rows
            DB::statement("UPDATE attendance_gates SET status = 'tidak_hadir' WHERE status = 'alpha'");
            DB::statement("UPDATE attendance_subject_details SET status = 'tidak_hadir' WHERE status = 'alpha'");
            return;
        }

        // ----------------------------------------------------------------
        // attendance_gates table
        // ----------------------------------------------------------------
        // Step 1: Add 'tidak_hadir' while keeping 'alpha' (so existing data is valid)
        DB::statement("ALTER TABLE attendance_gates MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");
        // Step 2: Update existing 'alpha' rows to 'tidak_hadir'
        DB::statement("UPDATE attendance_gates SET status = 'tidak_hadir' WHERE status = 'alpha'");
        // Step 3: Remove 'alpha' from ENUM definition
        DB::statement("ALTER TABLE attendance_gates MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");

        // ----------------------------------------------------------------
        // attendance_subject_details table
        // ----------------------------------------------------------------
        // Step 1: Add 'tidak_hadir' while keeping 'alpha'
        DB::statement("ALTER TABLE attendance_subject_details MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha', 'dispensasi', 'tidak_hadir') NOT NULL DEFAULT 'hadir'");
        // Step 2: Update existing 'alpha' rows to 'tidak_hadir'
        DB::statement("UPDATE attendance_subject_details SET status = 'tidak_hadir' WHERE status = 'alpha'");
        // Step 3: Remove 'alpha' from ENUM definition
        DB::statement("ALTER TABLE attendance_subject_details MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'tidak_hadir', 'dispensasi') NOT NULL DEFAULT 'hadir'");
    }

    public function down(): void
    {
        // ----------------------------------------------------------------
        // attendance_gates table
        // ----------------------------------------------------------------
        // Step 1: Add 'alpha' while keeping 'tidak_hadir'
        DB::statement("ALTER TABLE attendance_gates MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'tidak_hadir', 'alpha') NOT NULL DEFAULT 'hadir'");
        // Step 2: Revert 'tidak_hadir' rows back to 'alpha'
        DB::statement("UPDATE attendance_gates SET status = 'alpha' WHERE status = 'tidak_hadir'");
        // Step 3: Remove 'tidak_hadir' from ENUM definition
        DB::statement("ALTER TABLE attendance_gates MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'hadir'");

        // ----------------------------------------------------------------
        // attendance_subject_details table
        // ----------------------------------------------------------------
        // Step 1: Add 'alpha' while keeping 'tidak_hadir'
        DB::statement("ALTER TABLE attendance_subject_details MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'tidak_hadir', 'dispensasi', 'alpha') NOT NULL DEFAULT 'hadir'");
        // Step 2: Revert 'tidak_hadir' rows back to 'alpha'
        DB::statement("UPDATE attendance_subject_details SET status = 'alpha' WHERE status = 'tidak_hadir'");
        // Step 3: Remove 'tidak_hadir' from ENUM definition
        DB::statement("ALTER TABLE attendance_subject_details MODIFY COLUMN status ENUM('hadir', 'izin', 'sakit', 'alpha', 'dispensasi') NOT NULL DEFAULT 'hadir'");
    }
};