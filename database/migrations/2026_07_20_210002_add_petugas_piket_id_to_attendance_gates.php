<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom petugas_piket_id ke tabel attendance_gates.
     *
     * Kolom ini merujuk ke tabel petugas_piket untuk mencatat identitas
     * guru piket yang melakukan scan, TERPISAH dari kolom scanned_by (user_id).
     * Nullable karena scan dari admin tidak mengisi kolom ini.
     */
    public function up(): void
    {
        Schema::table('attendance_gates', function (Blueprint $table) {
            // FK ke petugas_piket.id — nullable karena hanya diisi saat scan dari role guru_piket
            $table->foreignId('petugas_piket_id')
                ->nullable()
                ->after('scanned_by')
                ->constrained('petugas_piket')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_gates', function (Blueprint $table) {
            $table->dropForeign(['petugas_piket_id']);
            $table->dropColumn('petugas_piket_id');
        });
    }
};