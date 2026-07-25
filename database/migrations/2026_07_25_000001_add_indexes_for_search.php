<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users — name sering dicari
        Schema::table('users', function (Blueprint $table) {
            $table->index('name');
        });

        // Teachers — udah ada index name, NIP/NUPTK unique = otomatis index ✅

        // Students — NIS/NISN/barcode_id unique (auto-indexed) ✅, name sudah index ✅

        // Parents — name biar gampang search
        Schema::table('parents', function (Blueprint $table) {
            $table->index('name');
        });

        // Classes — name perlu index
        Schema::table('classes', function (Blueprint $table) {
            $table->index('name');
        });

        // Majors — name perlu index
        Schema::table('majors', function (Blueprint $table) {
            $table->index('name');
        });

        // Subjects — name perlu index
        Schema::table('subjects', function (Blueprint $table) {
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('majors', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
