<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Column may already exist from a partial previous run — skip if so
            if (!Schema::hasColumn('classes', 'major_id')) {
                $table->foreignId('major_id')->nullable()->after('academic_year_id')->constrained('majors')->onDelete('cascade');
            }
        });

        // Add the composite index only if it doesn't already exist
        $indexes = DB::select("SHOW INDEX FROM `classes` WHERE Key_name = 'classes_grade_level_major_id_index'");
        if (empty($indexes)) {
            Schema::table('classes', function (Blueprint $table) {
                $table->index(['grade_level', 'major_id']);
            });
        }

        // Add the foreign key constraint only if it doesn't already exist
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'classes'
              AND CONSTRAINT_NAME = 'classes_major_id_foreign'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        if (empty($foreignKeys)) {
            Schema::table('classes', function (Blueprint $table) {
                $table->foreign('major_id')->references('id')->on('majors')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'major_id')) {
                $table->dropForeign(['major_id']);
                $table->dropColumn('major_id');
            }
        });
    }
};