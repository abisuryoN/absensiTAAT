<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        // Tahun Ajaran Aktif: 2025/2026
        $academicYear = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date' => '2025-07-14',
                'end_date'   => '2026-06-27',
                'is_active'  => true,
            ]
        );

        // Semester Ganjil (aktif)
        Semester::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'semester_number' => 1],
            [
                'name'       => 'Ganjil',
                'start_date' => '2025-07-14',
                'end_date'   => '2025-12-20',
                'is_active'  => true,
            ]
        );

        // Semester Genap (belum aktif)
        Semester::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'semester_number' => 2],
            [
                'name'       => 'Genap',
                'start_date' => '2026-01-05',
                'end_date'   => '2026-06-27',
                'is_active'  => false,
            ]
        );

        $this->command->info('✓ AcademicYear 2025/2026 + 2 Semesters created.');
    }
}