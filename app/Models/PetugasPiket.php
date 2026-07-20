<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetugasPiket extends Model
{
    protected $table = 'petugas_piket';

    protected $fillable = [
        'nama_lengkap',
    ];

    /**
     * Semua record absensi gerbang yang discan oleh petugas piket ini.
     */
    public function attendanceGates(): HasMany
    {
        return $this->hasMany(AttendanceGate::class, 'petugas_piket_id');
    }

    /**
     * Normalisasi nama: trim spasi, hilangkan double-space, Title Case.
     * Dipakai sebelum menyimpan dan sebelum pencocokan (lowercase untuk query).
     */
    public static function normalizeName(string $name): string
    {
        // Trim leading/trailing whitespace
        $name = trim($name);
        // Replace multiple consecutive spaces with single space
        $name = preg_replace('/\s+/', ' ', $name);
        // Apply Title Case (ucwords)
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

        return $name;
    }

    /**
     * Cari atau buat record petugas piket berdasarkan nama (case-insensitive).
     * Nama yang ditemukan menggunakan versi yang sudah tersimpan (konsisten).
     * Nama baru akan dinormalisasi dan disimpan sebagai Title Case.
     */
    public static function findOrCreateByName(string $inputName): self
    {
        $normalized = self::normalizeName($inputName);
        $lowerName  = mb_strtolower($normalized);

        // Cari dengan LOWER() agar case-insensitive match
        $existing = self::whereRaw('LOWER(nama_lengkap) = ?', [$lowerName])->first();

        if ($existing) {
            return $existing;
        }

        return self::create(['nama_lengkap' => $normalized]);
    }
}