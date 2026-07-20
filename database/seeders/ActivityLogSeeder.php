<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds activity_logs for both 2024/2025 (historical) and 2025/2026 (active).
 *
 * Actors:
 *   - super_admin role users  (master data, import/export, account ops)
 *   - guru_piket role users   (attendance scan & manual input)
 *
 * Modules: Absensi Gerbang | Data Siswa | Data Guru | Data Orang Tua |
 *          Data Kelas | Mata Pelajaran | Jurusan | Tahun Ajaran |
 *          Semester | Import/Export | Akun | Login
 */
class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ----------------------------------------------------------------
        // 1. Collect actor user IDs
        // ----------------------------------------------------------------
        $superAdminIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'super_admin')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('users.id')
            ->toArray();

        $guruPiketIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'guru_piket')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('users.id')
            ->toArray();

        // Fall back to first user if roles not seeded
        if (empty($superAdminIds)) {
            $superAdminIds = DB::table('users')->orderBy('id')->take(2)->pluck('id')->toArray();
        }
        if (empty($guruPiketIds)) {
            $guruPiketIds = $superAdminIds;
        }

        // ----------------------------------------------------------------
        // 2. Reference data (names for log descriptions)
        // ----------------------------------------------------------------
        $studentNames = DB::table('students')->orderByRaw('RAND()')->take(80)->pluck('name')->toArray();
        $teacherNames = DB::table('teachers')->orderByRaw('RAND()')->take(20)->pluck('name')->toArray();
        $parentNames  = DB::table('parents')->orderByRaw('RAND()')->take(20)->pluck('name')->toArray();
        $classNames   = DB::table('classes')->orderByRaw('RAND()')->take(20)->pluck('name')->toArray();
        $subjectNames = DB::table('subjects')->orderByRaw('RAND()')->take(10)->pluck('name')->toArray();

        $ipPool = [
            '192.168.1.100','192.168.1.101','192.168.1.102',
            '192.168.1.103','192.168.1.104','10.0.0.50','10.0.0.51',
        ];
        $uaPool = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/125.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/605.1.15',
            'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Chrome/124.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile Safari/604.1',
        ];

        if (empty($studentNames)) {
            $this->command->warn('No students found. Run StudentSeeder first.');
            return;
        }

        // ----------------------------------------------------------------
        // 3. Date ranges
        // ----------------------------------------------------------------
        $periods = [
            [
                'label'   => '2024/2025',
                'start'   => '2024-07-15',
                'end'     => '2025-06-28',
            ],
            [
                'label'   => '2025/2026',
                'start'   => '2025-07-14',
                'end'     => '2026-07-19', // current date (approx)
            ],
        ];

        $allLogs = [];

        foreach ($periods as $period) {
            $periodStart = new \DateTime($period['start']);
            $periodEnd   = new \DateTime($period['end']);
            $isHistoric  = ($period['label'] === '2024/2025');

            // Walk through weeks in period
            $current = clone $periodStart;
            $weekNum = 0;

            while ($current <= $periodEnd) {
                $dayOfWeek = (int) $current->format('N'); // 1=Mon 7=Sun
                $dateStr   = $current->format('Y-m-d');

                // Weekday only (Mon-Fri)
                if ($dayOfWeek <= 5) {
                    // --- Attendance scan logs (8-12 entries per day from guru piket) ---
                    $scanCount = $faker->numberBetween(8, 12);
                    for ($i = 0; $i < $scanCount; $i++) {
                        $stuName  = $faker->randomElement($studentNames);
                        $hour     = $faker->numberBetween(6, 7);
                        $minute   = str_pad($faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);
                        $allLogs[] = $this->makeLog(
                            $faker->randomElement($guruPiketIds),
                            'guru_piket',
                            'scan',
                            'Absensi Gerbang',
                            "Scan QR absensi masuk: {$stuName}",
                            $dateStr . " {$hour}:{$minute}:00",
                            $faker->randomElement($ipPool),
                            $faker->randomElement($uaPool)
                        );
                    }

                    // --- 1-3 manual attendance entries per day ---
                    $manualCount = $faker->numberBetween(1, 3);
                    for ($i = 0; $i < $manualCount; $i++) {
                        $stuName  = $faker->randomElement($studentNames);
                        $action   = $faker->randomElement(['input_manual', 'input_manual', 'mark_absent']);
                        $desc = $action === 'mark_absent'
                            ? 'Menandai ' . $stuName . ' sebagai ' . $faker->randomElement(['Izin', 'Sakit'])
                            : "Input manual absensi: {$stuName}";
                        $hour = $faker->numberBetween(7, 9);
                        $allLogs[] = $this->makeLog(
                            $faker->randomElement(array_merge($guruPiketIds, $superAdminIds)),
                            $faker->randomElement(['guru_piket', 'super_admin']),
                            $action,
                            'Absensi Gerbang',
                            $desc,
                            $dateStr . " {$hour}:" . str_pad($faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT) . ':00',
                            $faker->randomElement($ipPool),
                            $faker->randomElement($uaPool)
                        );
                    }

                    // --- Piket session start log (once per day) ---
                    $allLogs[] = $this->makeLog(
                        $faker->randomElement($guruPiketIds),
                        'guru_piket',
                        'create',
                        'Login',
                        'Sesi piket dimulai oleh: ' . $faker->randomElement(
                            array_map(function ($id) {
                                return DB::table('users')->where('id', $id)->value('name') ?? 'Guru Piket';
                            }, $guruPiketIds)
                        ),
                        $dateStr . ' 06:' . str_pad($faker->numberBetween(30, 59), 2, '0', STR_PAD_LEFT) . ':00',
                        $faker->randomElement($ipPool),
                        $faker->randomElement($uaPool)
                    );
                }

                // Weekly master data activities (from super admin, 1-2 per week on Mondays)
                if ($dayOfWeek === 1 && $weekNum % 2 === 0 && !empty($teacherNames)) {
                    $masterLogs = $this->generateMasterDataLogs(
                        $faker, $superAdminIds, $teacherNames, $studentNames,
                        $parentNames, $classNames, $subjectNames,
                        $dateStr, $ipPool, $uaPool
                    );
                    $allLogs = array_merge($allLogs, $masterLogs);
                }

                // Bi-weekly import/export activity (Fridays every 2 weeks)
                if ($dayOfWeek === 5 && $weekNum % 2 === 0) {
                    $allLogs[] = $this->makeLog(
                        $faker->randomElement($superAdminIds),
                        'super_admin',
                        'export',
                        'Import/Export',
                        'Mengunduh Rekap Absensi tanggal ' . $dateStr
                            . ' (format ' . $faker->randomElement(['Excel', 'PDF']) . ')',
                        $dateStr . ' ' . $faker->numberBetween(9, 15) . ':' . str_pad($faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT) . ':00',
                        $faker->randomElement($ipPool),
                        $faker->randomElement($uaPool)
                    );
                }

                // Monthly import (first Monday of each month)
                if ($dayOfWeek === 1 && (int) $current->format('j') <= 7) {
                    $count = $faker->numberBetween(30, 80);
                    $allLogs[] = $this->makeLog(
                        $faker->randomElement($superAdminIds),
                        'super_admin',
                        'import',
                        'Import/Export',
                        "Mengimpor {$count} data siswa dari file Excel",
                        $dateStr . ' ' . $faker->numberBetween(8, 11) . ':' . str_pad($faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT) . ':00',
                        $faker->randomElement($ipPool),
                        $faker->randomElement($uaPool)
                    );
                }

                // Year-transition events (June of historic year / July of current year)
                if ($isHistoric && $dateStr >= '2025-06-02' && $dateStr <= '2025-06-28' && $dayOfWeek === 1 && $weekNum % 3 === 0) {
                    $transitionLogs = $this->generateTransitionLogs($faker, $superAdminIds, $dateStr, $ipPool, $uaPool);
                    $allLogs = array_merge($allLogs, $transitionLogs);
                }
                if (!$isHistoric && $dateStr >= '2025-07-14' && $dateStr <= '2025-07-31' && $dayOfWeek === 1) {
                    $transitionLogs = $this->generateTransitionLogs($faker, $superAdminIds, $dateStr, $ipPool, $uaPool);
                    $allLogs = array_merge($allLogs, $transitionLogs);
                }

                $current->modify('+1 day');
                if ($dayOfWeek === 1) {
                    $weekNum++;
                }

                // Insert in chunks to avoid memory issues
                if (count($allLogs) >= 500) {
                    DB::table('activity_logs')->insert($allLogs);
                    $allLogs = [];
                }
            }
        }

        if (!empty($allLogs)) {
            DB::table('activity_logs')->insert($allLogs);
        }

        $total = DB::table('activity_logs')->count();
        $this->command->info("Activity logs seeded. Total records: {$total}");
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function makeLog(
        int $userId,
        string $causerRole,
        string $action,
        string $module,
        string $description,
        string $createdAt,
        string $ip,
        string $ua
    ): array {
        return [
            'user_id'     => $userId,
            'causer_role' => $causerRole,
            'action'      => $action,
            'module'      => $module,
            'model_type'  => null,
            'model_id'    => null,
            'description' => $description,
            'properties'  => null,
            'ip_address'  => $ip,
            'user_agent'  => $ua,
            'created_at'  => $createdAt,
            'updated_at'  => $createdAt,
        ];
    }

    private function generateMasterDataLogs(
        $faker,
        array $superAdminIds,
        array $teacherNames,
        array $studentNames,
        array $parentNames,
        array $classNames,
        array $subjectNames,
        string $date,
        array $ipPool,
        array $uaPool
    ): array {
        $logs     = [];
        $adminId  = $faker->randomElement($superAdminIds);
        $ip       = $faker->randomElement($ipPool);
        $ua       = $faker->randomElement($uaPool);
        $baseHour = $faker->numberBetween(8, 14);
        $baseMins = str_pad($faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);
        $ts       = "{$date} {$baseHour}:{$baseMins}:00";

        $actions = $faker->randomElements([
            ['create', 'Data Siswa',      'Menambah data siswa: ' . $faker->randomElement($studentNames)],
            ['update', 'Data Siswa',      'Mengubah data siswa: ' . $faker->randomElement($studentNames)],
            ['create', 'Data Guru',       'Menambah data guru: ' . $faker->randomElement($teacherNames)],
            ['update', 'Data Guru',       'Mengubah data guru: ' . $faker->randomElement($teacherNames)],
            ['create', 'Data Orang Tua',  'Menambah data orang tua/wali: ' . $faker->randomElement($parentNames)],
            ['update', 'Data Kelas',      'Mengubah data kelas: ' . $faker->randomElement($classNames)],
            ['create', 'Mata Pelajaran',  'Menambah mata pelajaran: ' . $faker->randomElement($subjectNames)],
            ['update', 'Jurusan',         'Menambah/mengubah data jurusan'],
            ['export', 'Import/Export',   'Mengunduh Template Import Siswa'],
            ['export', 'Import/Export',   'Mengunduh Referensi Data Orang Tua'],
        ], $faker->numberBetween(1, 3));

        foreach ($actions as $act) {
            $logs[] = $this->makeLog($adminId, 'super_admin', $act[0], $act[1], $act[2], $ts, $ip, $ua);
        }

        return $logs;
    }

    private function generateTransitionLogs(
        $faker,
        array $superAdminIds,
        string $date,
        array $ipPool,
        array $uaPool
    ): array {
        $logs    = [];
        $adminId = $faker->randomElement($superAdminIds);
        $ip      = $faker->randomElement($ipPool);
        $ua      = $faker->randomElement($uaPool);
        $hour    = $faker->numberBetween(9, 14);
        $min     = str_pad($faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);
        $ts      = "{$date} {$hour}:{$min}:00";

        $transitionActions = [
            ['update', 'Tahun Ajaran', 'Memproses kenaikan kelas 10 ke 11 (penjurusan): 184 siswa'],
            ['update', 'Tahun Ajaran', 'Memproses kenaikan kelas 11 ke 12: 187 siswa'],
            ['update', 'Tahun Ajaran', 'Memproses kelulusan siswa kelas 12: 510 siswa lulus'],
            ['create', 'Tahun Ajaran', 'Membuat tahun ajaran baru: 2025/2026'],
            ['create', 'Semester',     'Membuat semester baru: Ganjil 2025/2026'],
            ['update', 'Tahun Ajaran', 'Mengaktifkan Tahun Ajaran baru: 2025/2026'],
        ];

        $sample = $faker->randomElements($transitionActions, $faker->numberBetween(1, 3));
        foreach ($sample as $act) {
            $logs[] = $this->makeLog($adminId, 'super_admin', $act[0], $act[1], $act[2], $ts, $ip, $ua);
        }

        return $logs;
    }
}