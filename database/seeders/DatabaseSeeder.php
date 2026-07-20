<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Urutan sesuai FK dependency:
     *  1.  RoleSeeder                 — roles & permissions (Spatie)
     *  2.  SettingSeeder              — pengaturan aplikasi (jika ada)
     *  3.  AcademicYearSeeder         — tahun ajaran 2025/2026 + 2 semester
     *  4.  MajorSeeder                — UMUM, IPA, IPS, BAHASA
     *  5.  SubjectSeeder              — 17 mata pelajaran
     *  6.  TeacherSeeder              — 28 guru + akun login (role: guru)
     *  7.  ClassSeeder                — 24 kelas 2025/2026 + wali kelas
     *  8.  SuperAdminSeeder           — 2 akun super admin
     *  9.  GuruPiketSeeder            — 1 akun shared guru piket
     *  10. ParentSeeder               — 494 orang tua + akun login (role: parent)
     *  11. StudentSeeder              — 548 siswa + akun login (role: siswa)
     *  12. AttendanceSeeder           — 14 hari data absensi 2025/2026
     *  13. PreviousYearDataSeeder     — tahun ajaran 2024/2025 (historis) + 510 alumni
     *  14. HistoricalAttendanceSeeder — 20 hari absensi historis 2024/2025
     *  15. ActivityLogSeeder          — log aktivitas kedua tahun ajaran
     *
     * Jalankan:
     *   php artisan migrate:fresh --seed   (fresh install)
     *   php artisan db:seed                (seed saja, tanpa reset)
     */
    public function run(): void
    {
        $this->call([
            // Foundational
            RoleSeeder::class,
            SettingSeeder::class,
            WhatsappTemplateSeeder::class,
            SchoolProfileSeeder::class,

            // Master data
            AcademicYearSeeder::class,
            MajorSeeder::class,
            SubjectSeeder::class,

            // People — guru dulu (dipakai di ClassSeeder untuk homeroom_teacher_id)
            TeacherSeeder::class,
            ClassSeeder::class,

            // Akun admin
            SuperAdminSeeder::class,
            GuruPiketSeeder::class,

            // Siswa & orang tua
            ParentSeeder::class,
            StudentSeeder::class,

            // Data absensi tahun ajaran aktif (2025/2026)
            AttendanceSeeder::class,

            // Data historis tahun ajaran 2024/2025 (termasuk 510 alumni)
            PreviousYearDataSeeder::class,
            HistoricalAttendanceSeeder::class,

            // Log aktivitas untuk kedua tahun ajaran
            ActivityLogSeeder::class,
        ]);
    }
}