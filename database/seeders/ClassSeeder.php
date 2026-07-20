<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * 24 kelas: 8 X (UMUM) + 8 XI (IPA×4, IPS×3, Bahasa×1) + 8 XII (same pattern).
     * homeroom_teacher_id diambil dari Teacher berurutan (teacher index 0–23).
     */
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025/2026')->firstOrFail();

        $majorUmum   = Major::where('code', 'UMUM')->firstOrFail();
        $majorIPA    = Major::where('code', 'IPA')->firstOrFail();
        $majorIPS    = Major::where('code', 'IPS')->firstOrFail();
        $majorBahasa = Major::where('code', 'BAHASA')->firstOrFail();

        // Get teachers ordered by their creation (matches TeacherSeeder order, index 0-23)
        $teachers = Teacher::orderBy('id')->take(24)->pluck('id')->toArray();

        if (count($teachers) < 24) {
            $this->command->warn('⚠ Less than 24 teachers found. Run TeacherSeeder first.');
            return;
        }

        // [grade_level, major, class_name, capacity, teacher_index]
        $classes = [
            // Kelas X — belum penjurusan
            [10, $majorUmum->id,   'X-1',          32, 0],
            [10, $majorUmum->id,   'X-2',          32, 1],
            [10, $majorUmum->id,   'X-3',          34, 2],
            [10, $majorUmum->id,   'X-4',          32, 3],
            [10, $majorUmum->id,   'X-5',          34, 4],
            [10, $majorUmum->id,   'X-6',          32, 5],
            [10, $majorUmum->id,   'X-7',          34, 6],
            [10, $majorUmum->id,   'X-8',          32, 7],
            // Kelas XI
            [11, $majorIPA->id,    'XI IPA 1',     34, 8],
            [11, $majorIPA->id,    'XI IPA 2',     34, 9],
            [11, $majorIPA->id,    'XI IPA 3',     34, 10],
            [11, $majorIPA->id,    'XI IPA 4',     32, 11],
            [11, $majorIPS->id,    'XI IPS 1',     32, 12],
            [11, $majorIPS->id,    'XI IPS 2',     32, 13],
            [11, $majorIPS->id,    'XI IPS 3',     30, 14],
            [11, $majorBahasa->id, 'XI Bahasa 1',  28, 15],
            // Kelas XII
            [12, $majorIPA->id,    'XII IPA 1',    34, 16],
            [12, $majorIPA->id,    'XII IPA 2',    34, 17],
            [12, $majorIPA->id,    'XII IPA 3',    34, 18],
            [12, $majorIPA->id,    'XII IPA 4',    32, 19],
            [12, $majorIPS->id,    'XII IPS 1',    32, 20],
            [12, $majorIPS->id,    'XII IPS 2',    32, 21],
            [12, $majorIPS->id,    'XII IPS 3',    30, 22],
            [12, $majorBahasa->id, 'XII Bahasa 1', 28, 23],
        ];

        foreach ($classes as [$grade, $majorId, $name, $capacity, $teacherIdx]) {
            SchoolClass::updateOrCreate(
                ['academic_year_id' => $academicYear->id, 'name' => $name],
                [
                    'academic_year_id'    => $academicYear->id,
                    'major_id'            => $majorId,
                    'grade_level'         => $grade,
                    'name'                => $name,
                    'capacity'            => $capacity,
                    'homeroom_teacher_id' => $teachers[$teacherIdx],
                    'is_active'           => true,
                ]
            );
        }

        $this->command->info('✓ 24 Classes created (8×X + 8×XI + 8×XII).');
    }
}