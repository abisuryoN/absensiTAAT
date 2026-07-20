<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Generate ~548 siswa tersebar di 24 kelas.
     * Grade 10: tahun_masuk 2025 | Grade 11: 2024 | Grade 12: 2023
     * NIS: [tahun_masuk][4-digit urut], NISN: 10-digit
     * 90% siswa punya parent unik, 10% share parent (kakak-adik)
     * Semua password: password123
     */
    public function run(): void
    {
        $faker    = Faker::create('id_ID');
        $password = Hash::make('password123');
        $now      = now()->toDateTimeString();

        $academicYear = AcademicYear::where('name', '2025/2026')->firstOrFail();

        $classes = SchoolClass::where('academic_year_id', $academicYear->id)
            ->orderBy('id')
            ->get(['id', 'grade_level', 'name']);

        if ($classes->isEmpty()) {
            $this->command->warn('No classes found. Run ClassSeeder first.');
            return;
        }

        $parentIds = DB::table('parents')->orderBy('id')->pluck('id')->toArray();
        if (empty($parentIds)) {
            $this->command->warn('No parents found. Run ParentSeeder first.');
            return;
        }

        $siswaRoleId = DB::table('roles')->where('name', 'siswa')->value('id');

        $studentsPerClass = [
            'X-1' => 24, 'X-2' => 23, 'X-3' => 25, 'X-4' => 22,
            'X-5' => 24, 'X-6' => 23, 'X-7' => 25, 'X-8' => 22,
            'XI IPA 1' => 26, 'XI IPA 2' => 25, 'XI IPA 3' => 24, 'XI IPA 4' => 23,
            'XI IPS 1' => 22, 'XI IPS 2' => 23, 'XI IPS 3' => 21, 'XI Bahasa 1' => 20,
            'XII IPA 1' => 25, 'XII IPA 2' => 24, 'XII IPA 3' => 23, 'XII IPA 4' => 22,
            'XII IPS 1' => 22, 'XII IPS 2' => 21, 'XII IPS 3' => 20, 'XII Bahasa 1' => 19,
        ];

        $nisCounters    = [2023 => 1, 2024 => 1, 2025 => 1];
        $nisnBase       = 12345000;
        $parentUsageIdx = 0;

        $maleFirstNames = [
            'Adi', 'Agung', 'Ahmad', 'Aldi', 'Alif', 'Andika', 'Arif', 'Aryo',
            'Bagas', 'Bagus', 'Bima', 'Dafa', 'Danu', 'Dimas', 'Eko', 'Fajar',
            'Farhan', 'Fikri', 'Galang', 'Gilang', 'Hafiz', 'Ilham', 'Ivan',
            'Kevin', 'Krisna', 'Luthfi', 'Mahendra', 'Malik', 'Nanda', 'Pandu',
            'Putra', 'Rafi', 'Rahmat', 'Rangga', 'Reza', 'Rifki', 'Rio',
            'Rizki', 'Rizal', 'Satria', 'Tegar', 'Wahyu', 'Yogi', 'Yusuf',
            'Zaidan', 'Zaki', 'Dandi', 'Brian', 'Novian', 'Vino',
        ];
        $femaleFirstNames = [
            'Adinda', 'Alya', 'Amanda', 'Amelia', 'Anisa', 'Ayu', 'Bunga',
            'Cahya', 'Cindy', 'Dea', 'Desi', 'Dewi', 'Diana', 'Dinda', 'Elsa',
            'Farah', 'Fatimah', 'Fitri', 'Ghina', 'Hana', 'Indah', 'Intan',
            'Julia', 'Karina', 'Laila', 'Lathifa', 'Maya', 'Mega', 'Nabila',
            'Nadia', 'Nafisa', 'Nisa', 'Novita', 'Nurul', 'Putri', 'Rini',
            'Rizka', 'Rosa', 'Salsabila', 'Sari', 'Sinta', 'Siti', 'Tiara',
            'Ulfa', 'Vina', 'Wulan', 'Yuli', 'Zahra', 'Ristia', 'Okta',
        ];
        $lastNames = [
            'Santoso', 'Wijaya', 'Kusuma', 'Saputra', 'Rahayu', 'Hidayat',
            'Purnomo', 'Wibowo', 'Susanto', 'Prasetyo', 'Hartono', 'Nugroho',
            'Setiawan', 'Gunawan', 'Firmansyah', 'Ramadhan', 'Budiman', 'Mulyono',
            'Sulistyo', 'Putra', 'Nugraha', 'Mahendra', 'Maulana', 'Fauzi',
            'Wahyudi', 'Kusumawati', 'Lestari', 'Andriani', 'Permata', 'Azzahra',
            'Salma', 'Fadillah', 'Perdana', 'Islami', 'Hakim',
        ];
        $birthPlaces = [
            'Bogor', 'Depok', 'Bekasi', 'Tangerang', 'Jakarta', 'Bandung',
            'Sukabumi', 'Cianjur', 'Tasikmalaya', 'Garut', 'Cirebon', 'Karawang',
        ];
        $streets = [
            'Jl. Raya Tajurhalang', 'Jl. Ciseeng', 'Jl. Parung', 'Jl. Ciampea',
            'Jl. Leuwiliang', 'Jl. Ciawi', 'Jl. Bojonggede', 'Jl. Citayam',
            'Jl. Raya Parung', 'Jl. Sawangan', 'Perum Griya Tajurhalang',
            'Jl. Gunung Sindur', 'Jl. Ciputat Raya',
        ];
        $kelurahans = ['Tajurhalang', 'Ciseeng', 'Parung', 'Gunung Sindur', 'Bojonggede'];

        $usersToInsert    = [];
        $studentsToInsert = [];
        $emailsUsed       = [];

        foreach ($classes as $class) {
            $gradeName  = $class->name;
            $gradeLevel = $class->grade_level;
            $count      = $studentsPerClass[$gradeName] ?? 22;

            $tahunMasuk = match ($gradeLevel) {
                12      => 2023,
                11      => 2024,
                default => 2025,
            };

            for ($s = 0; $s < $count; $s++) {
                $gender    = ($s % 2 === 0) ? 'L' : 'P';
                $firstName = $gender === 'L'
                    ? $faker->randomElement($maleFirstNames)
                    : $faker->randomElement($femaleFirstNames);
                $lastName  = $faker->randomElement($lastNames);
                $name      = "{$firstName} {$lastName}";

                $nis = (string) $tahunMasuk . str_pad($nisCounters[$tahunMasuk], 4, '0', STR_PAD_LEFT);
                $nisCounters[$tahunMasuk]++;
                $nisn      = str_pad($nisnBase++, 10, '0', STR_PAD_LEFT);
                $barcodeId = 'SMAN1TJR-' . $nis;

                $ageYears = match ($gradeLevel) {
                    12      => $faker->numberBetween(17, 18),
                    11      => $faker->numberBetween(16, 17),
                    default => $faker->numberBetween(15, 16),
                };
                $birthYear  = 2026 - $ageYears;
                $birthMonth = str_pad($faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
                $birthDay   = str_pad($faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT);
                $birthDate  = "{$birthYear}-{$birthMonth}-{$birthDay}";

                $birthPlace = $faker->randomElement($birthPlaces);
                $address    = $faker->randomElement($streets) . ' No. '
                    . $faker->numberBetween(1, 200)
                    . ', Kel. ' . $faker->randomElement($kelurahans) . ', Kab. Bogor';

                $phonePrefix = $faker->randomElement(['0811', '0812', '0813', '0821', '0857', '0858']);
                $phone       = $faker->optional(0.7)->numerify($phonePrefix . '########');

                $email = "siswa.{$nis}@sman1tajurhalang.sch.id";
                if (in_array($email, $emailsUsed)) {
                    $email = "siswa.{$nis}x@sman1tajurhalang.sch.id";
                }
                $emailsUsed[] = $email;

                // 90% unique parent, 10% shared (siblings)
                if ($parentUsageIdx < count($parentIds)) {
                    $parentId = $parentIds[$parentUsageIdx];
                    $parentUsageIdx++;
                } else {
                    $parentId = $parentIds[$faker->numberBetween(0, min(99, count($parentIds) - 1))];
                }

                $isActive = ($faker->numberBetween(1, 100) <= 97);

                $usersToInsert[] = [
                    'name'       => $name,
                    'email'      => $email,
                    'password'   => $password,
                    'is_active'  => $isActive,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $studentsToInsert[] = [
                    'parent_id'   => $parentId,
                    'class_id'    => $class->id,
                    'nis'         => $nis,
                    'nisn'        => $nisn,
                    'name'        => $name,
                    'gender'      => $gender,
                    'phone'       => $phone,
                    'birth_date'  => $birthDate,
                    'birth_place' => $birthPlace,
                    'address'     => $address,
                    'barcode_id'  => $barcodeId,
                    'is_active'   => $isActive,
                    'tahun_masuk' => $tahunMasuk,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    // user_id filled after user insert below
                    '_class_name' => $gradeName, // temp key, stripped before DB insert
                ];
            }
        }

        $totalStudents = count($usersToInsert);
        $chunkSize     = 50;

        // 1. Bulk-insert user accounts
        foreach (array_chunk($usersToInsert, $chunkSize) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // 2. Fetch the IDs of the just-inserted users (last N by ID, in insertion order)
        $userIds = array_reverse(
            DB::table('users')
                ->orderBy('id', 'desc')
                ->take($totalStudents)
                ->pluck('id')
                ->toArray()
        );

        // 3. Bulk-assign 'siswa' role
        if ($siswaRoleId && count($userIds) === $totalStudents) {
            $roleRows = [];
            foreach ($userIds as $uid) {
                $roleRows[] = [
                    'role_id'    => $siswaRoleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $uid,
                ];
            }
            foreach (array_chunk($roleRows, $chunkSize) as $chunk) {
                DB::table('model_has_roles')->insertOrIgnore($chunk);
            }
        }

        // 4. Attach user_id and remove temp key, then bulk-insert students
        foreach ($studentsToInsert as $idx => &$student) {
            $student['user_id'] = $userIds[$idx] ?? null;
            unset($student['_class_name']);
        }
        unset($student);

        foreach (array_chunk($studentsToInsert, $chunkSize) as $chunk) {
            DB::table('students')->insert($chunk);
        }

        $this->command->info("✓ {$totalStudents} Students created across 24 classes.");
    }
}