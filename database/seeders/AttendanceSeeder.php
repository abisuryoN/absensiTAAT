<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Generate 14 hari data absensi gerbang untuk semua siswa aktif.
     *
     * Distribusi status (realistis):
     *  - hadir    : 80%
     *  - terlambat: 8%
     *  - izin     : 4%
     *  - sakit    : 4%
     *  - (alpha)  : 4% — no record at all
     *
     * Waktu scan:
     *  - hadir    : 06:15 – 07:00
     *  - terlambat: 07:01 – 09:30
     *  - izin/sakit: dikasih time_in 00:00:00 (manual entry)
     *
     * Scanner: nama petugas piket acak dari pool kecil
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $academicYear = AcademicYear::where('name', '2025/2026')->first();
        $semester     = Semester::where('academic_year_id', $academicYear?->id)
            ->where('is_active', true)
            ->first();

        if (!$academicYear || !$semester) {
            $this->command->warn('No active academic year/semester found. Skipping AttendanceSeeder.');
            return;
        }

        // Get active student IDs
        $studentIds = DB::table('students')
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            $this->command->warn('No active students found. Run StudentSeeder first.');
            return;
        }

        // Get the guru piket user as scanner
        $scannerUser = DB::table('users')
            ->where('email', 'piket@sman1tajurhalang.sch.id')
            ->value('id');

        // Petugas piket pool — insert a few petugas piket records
        $petugasNames = [
            'Pak Budi Santoso', 'Bu Siti Rahayu', 'Pak Ahmad Fauzi',
            'Bu Dewi Kusuma', 'Pak Hendra Saputra',
        ];
        foreach ($petugasNames as $namaLengkap) {
            DB::table('petugas_piket')->insertOrIgnore([
                'nama_lengkap' => $namaLengkap,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
        $petugasIds = DB::table('petugas_piket')->pluck('id')->toArray();

        $now      = now();
        $today    = $now->copy()->startOfDay();

        // Hari kerja aktif saja (Senin–Jumat), 14 hari ke belakang
        $workDays = [];
        $check    = $today->copy()->subDays(1);
        while (count($workDays) < 14) {
            if ($check->isWeekday()) {
                $workDays[] = $check->toDateString();
            }
            $check->subDay();
        }

        $records   = [];
        $batchSize = 200;
        $inserted  = 0;

        // Status weights: 80% hadir, 8% terlambat, 4% izin, 4% sakit, 4% alpha (skip)
        $statusPool = array_merge(
            array_fill(0, 80, 'hadir'),
            array_fill(0, 8, 'terlambat'),
            array_fill(0, 4, 'izin'),
            array_fill(0, 4, 'sakit'),
            array_fill(0, 4, 'alpha') // alpha = no record
        );

        foreach ($workDays as $date) {
            foreach ($studentIds as $studentId) {
                $statusRoll = $faker->randomElement($statusPool);

                if ($statusRoll === 'alpha') {
                    continue; // No record for alpha
                }

                $timeIn = match ($statusRoll) {
                    'hadir'     => sprintf('%02d:%02d:00', 6, $faker->numberBetween(15, 59)),
                    'terlambat' => sprintf('%02d:%02d:00',
                        $faker->numberBetween(7, 9),
                        $faker->numberBetween(1, 59)
                    ),
                    default     => '00:00:00', // izin / sakit
                };

                $records[] = [
                    'student_id'       => $studentId,
                    'academic_year_id' => $academicYear->id,
                    'semester_id'      => $semester->id,
                    'date'             => $date,
                    'time_in'          => $timeIn,
                    'status'           => $statusRoll,
                    'method'           => $faker->randomElement(['barcode', 'barcode', 'barcode', 'manual']),
                    'note'             => null,
                    'scanned_by'       => $scannerUser,
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

        // Insert remaining
        if (!empty($records)) {
            DB::table('attendance_gates')->insertOrIgnore($records);
            $inserted += count($records);
        }

        $this->command->info("✓ ~{$inserted} Attendance records created for 14 working days.");
    }
}