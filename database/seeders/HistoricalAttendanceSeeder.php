<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generates historical attendance records for the 2024/2025 academic year.
 *
 * Coverage: 20 sample weekdays in Aug-Oct 2024 (Semester Ganjil 2024/2025).
 * Students included:
 *   - Current grade-11 students (were grade 10 in 2024/2025)
 *   - Current grade-12 students (were grade 11 in 2024/2025)
 *   - Alumni students (were grade 12 in 2024/2025, tahun_masuk = 2022)
 *
 * Method logic (same as updated AttendanceSeeder):
 *   - izin / sakit : always 'manual'
 *   - hadir / terlambat : 88% 'barcode', 12% 'manual'
 */
class HistoricalAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $prevYear = AcademicYear::where('name', '2024/2025')->first();
        if (!$prevYear) {
            $this->command->warn('2024/2025 academic year not found. Run PreviousYearDataSeeder first.');
            return;
        }

        $semGanjil = DB::table('semesters')
            ->where('academic_year_id', $prevYear->id)
            ->where('name', 'Ganjil')
            ->first();

        if (!$semGanjil) {
            $this->command->warn('Semester Ganjil 2024/2025 not found. Skipping.');
            return;
        }

        // ----------------------------------------------------------------
        // 1. Collect student IDs active during 2024/2025
        //    a) Current grade 11 (were grade 10)
        //    b) Current grade 12 (were grade 11)
        //    c) Alumni (tahun_masuk = 2022, is_active = false)
        // ----------------------------------------------------------------
        $currYear     = AcademicYear::where('name', '2025/2026')->firstOrFail();
        $currClasses  = DB::table('classes')
            ->where('academic_year_id', $currYear->id)
            ->pluck('id', 'grade_level') // not unique, use get()
            ->toArray();

        // Grade 11 current class IDs
        $grade11ClassIds = DB::table('classes')
            ->where('academic_year_id', $currYear->id)
            ->where('grade_level', 11)
            ->pluck('id')
            ->toArray();

        // Grade 12 current class IDs
        $grade12ClassIds = DB::table('classes')
            ->where('academic_year_id', $currYear->id)
            ->where('grade_level', 12)
            ->pluck('id')
            ->toArray();

        $activeStudentIds = DB::table('students')
            ->where('is_active', true)
            ->whereIn('class_id', array_merge($grade11ClassIds, $grade12ClassIds))
            ->pluck('id')
            ->toArray();

        // Alumni students
        $alumniStudentIds = DB::table('students')
            ->where('is_active', false)
            ->where('tahun_masuk', 2022)
            ->pluck('id')
            ->toArray();

        $studentIds = array_merge($activeStudentIds, $alumniStudentIds);

        if (empty($studentIds)) {
            $this->command->warn('No students found for historical attendance. Check PreviousYearDataSeeder.');
            return;
        }

        // ----------------------------------------------------------------
        // 2. Petugas piket pool (reuse existing, or insert placeholders)
        // ----------------------------------------------------------------
        $petugasIds = DB::table('petugas_piket')->pluck('id')->toArray();
        if (empty($petugasIds)) {
            $petugasNames = [
                'Pak Budi Santoso', 'Bu Siti Rahayu', 'Pak Ahmad Fauzi',
                'Bu Dewi Kusuma', 'Pak Hendra Saputra',
            ];
            foreach ($petugasNames as $nama) {
                DB::table('petugas_piket')->insertOrIgnore([
                    'nama_lengkap' => $nama,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
            $petugasIds = DB::table('petugas_piket')->pluck('id')->toArray();
        }

        $scannerUserId = DB::table('users')
            ->where('email', 'piket@sman1tajurhalang.sch.id')
            ->value('id');

        // ----------------------------------------------------------------
        // 3. Generate 20 sample weekdays in Aug-Oct 2024
        // ----------------------------------------------------------------
        $sampleDates = [];
        $sampleStartDays = [
            // August 2024 (8 days)
            '2024-08-05', '2024-08-07', '2024-08-12', '2024-08-14',
            '2024-08-19', '2024-08-21', '2024-08-26', '2024-08-28',
            // September 2024 (7 days)
            '2024-09-02', '2024-09-04', '2024-09-09', '2024-09-11',
            '2024-09-16', '2024-09-18', '2024-09-23',
            // October 2024 (5 days)
            '2024-10-01', '2024-10-07', '2024-10-14', '2024-10-21', '2024-10-28',
        ];

        // Status pool: 80% hadir, 8% terlambat, 4% izin, 4% sakit, 4% alpha
        $statusPool = array_merge(
            array_fill(0, 80, 'hadir'),
            array_fill(0, 8, 'terlambat'),
            array_fill(0, 4, 'izin'),
            array_fill(0, 4, 'sakit'),
            array_fill(0, 4, 'alpha')
        );

        // Method pool for hadir/terlambat: 88% barcode, 12% manual
        $methodPoolScan = array_merge(
            array_fill(0, 88, 'barcode'),
            array_fill(0, 12, 'manual')
        );

        $records   = [];
        $batchSize = 300;
        $inserted  = 0;

        foreach ($sampleStartDays as $date) {
            foreach ($studentIds as $studentId) {
                $status = $faker->randomElement($statusPool);

                if ($status === 'alpha') {
                    continue;
                }

                // Method: izin/sakit always manual; hadir/terlambat mostly barcode
                $method = match (true) {
                    in_array($status, ['izin', 'sakit']) => 'manual',
                    default => $faker->randomElement($methodPoolScan),
                };

                $timeIn = match ($status) {
                    'hadir'     => sprintf('%02d:%02d:00', 6, $faker->numberBetween(15, 59)),
                    'terlambat' => sprintf('%02d:%02d:00',
                        $faker->numberBetween(7, 9),
                        $faker->numberBetween(1, 59)
                    ),
                    default     => '00:00:00',
                };

                $records[] = [
                    'student_id'       => $studentId,
                    'academic_year_id' => $prevYear->id,
                    'semester_id'      => $semGanjil->id,
                    'date'             => $date,
                    'time_in'          => $timeIn,
                    'status'           => $status,
                    'method'           => $method,
                    'note'             => null,
                    'scanned_by'       => $scannerUserId,
                    'petugas_piket_id' => $faker->randomElement($petugasIds),
                    'created_at'       => $date . ' ' . $timeIn,
                    'updated_at'       => $date . ' ' . $timeIn,
                ];

                if (count($records) >= $batchSize) {
                    DB::table('attendance_gates')->insertOrIgnore($records);
                    $inserted += count($records);
                    $records = [];
                }
            }
        }

        if (!empty($records)) {
            DB::table('attendance_gates')->insertOrIgnore($records);
            $inserted += count($records);
        }

        $this->command->info("Historical attendance: ~{$inserted} records for 20 days in 2024/2025.");
    }
}