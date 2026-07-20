<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan identitas petugas piket gerbang yang INDEPENDEN
     * dari akun login. Satu nama = satu identitas, dipakai oleh sesi-sesi
     * berbeda selama nama piket yang diinput (setelah normalisasi) cocok.
     *
     * Normalisasi yang diterapkan saat insert: trim + hilangkan double-space
     * + Title Case (dilakukan di controller, bukan di sini).
     */
    public function up(): void
    {
        Schema::create('petugas_piket', function (Blueprint $table) {
            $table->id();
            // Nama tampilan yang sudah dinormalisasi (Title Case, trim, single-space)
            $table->string('nama_lengkap');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas_piket');
    }
};