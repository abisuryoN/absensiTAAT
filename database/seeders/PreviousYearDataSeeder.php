<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the full 2024/2025 historical academic year.
 *
 * - Academic year + 2 semesters (inactive)
 * - 24 historical classes (8x X, 8x XI, 8x XII-alumni)
 * - Homeroom teachers (only those whose tahun_masuk_kerja < 2024)
 * - class_student_history for current grade-11 to historical X classes
 * - class_student_history for current grade-12 to historical XI classes
 * - 510 alumni students (grade 12 in 2024/2025, now graduated)
 * - class_student_history for alumni to historical XII classes
 * - class_student_history for 2025/2026 current students (current year snapshot)
 */
class PreviousYearDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker    = Faker::create('id_ID');
        $password = Hash::make('password123');
        $now      = now()->toDateTimeString();

        // ----------------------------------------------------------------
        // 1. Academic Year 2024/2025 (inactive)
        // ----------------------------------------------------------------
        $prevYear = AcademicYear::updateOrCreate(
            ['name' => '2024/2025'],
            [
                'start_date' => '2024-07-15',
                'end_date'   => '2025-06-28',
                'is_active'  => false,
            ]
        );

        // Semesters
        DB::table('semesters')->updateOrInsert(
            ['academic_year_id' => $prevYear->id, 'semester_number' => 1],
            [
                'academic_year_id' => $prevYear->id,
                'semester_number'  => 1,
                'name'             => 'Ganjil',
                'start_date'       => '2024-07-15',
                'end_date'         => '2024-12-21',
                'is_active'        => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );
        $semGanjilId = DB::table('semesters')
            ->where('academic_year_id', $prevYear->id)
            ->where('semester_number', 1)
            ->value('id');

        DB::table('semesters')->updateOrInsert(
            ['academic_year_id' => $prevYear->id, 'semester_number' => 2],
            [
                'academic_year_id' => $prevYear->id,
                'semester_number'  => 2,
                'name'             => 'Genap',
                'start_date'       => '2025-01-06',
                'end_date'         => '2025-06-28',
                'is_active'        => false,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );

        $this->command->info('✓ Academic Year 2024/2025 + semesters created.');

        // ----------------------------------------------------------------
        // 2. Majors
        // ----------------------------------------------------------------
        $majorUmum   = Major::where('code', 'UMUM')->firstOrFail();
        $majorIPA    = Major::where('code', 'IPA')->firstOrFail();
        $majorIPS    = Major::where('code', 'IPS')->firstOrFail();
        $majorBahasa = Major::where('code', 'BAHASA')->firstOrFail();

        // ----------------------------------------------------------------
        // 3. Teachers eligible for 2024/2025 (joined before 2024)
        // ----------------------------------------------------------------
        $eligibleTeachers = Teacher::where('tahun_masuk_kerja', '<', 2024)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        // Ensure at least 24 slots (cycle if needed)
        while (count($eligibleTeachers) < 24) {
            $eligibleTeachers = array_merge($eligibleTeachers, $eligibleTeachers);
        }

        // ----------------------------------------------------------------
        // 4. Create 24 historical classes for 2024/2025
        //    Same layout as current year but tied to prevYear
        // ----------------------------------------------------------------
        $classDefs = [
            // Grade 10 (UMUM) — will hold students now in grade 11
            [10, $majorUmum->id,   'X-1',          32, 0],
            [10, $majorUmum->id,   'X-2',          32, 1],
            [10, $majorUmum->id,   'X-3',          34, 2],
            [10, $majorUmum->id,   'X-4',          32, 3],
            [10, $majorUmum->id,   'X-5',          34, 4],
            [10, $majorUmum->id,   'X-6',          32, 5],
            [10, $majorUmum->id,   'X-7',          34, 6],
            [10, $majorUmum->id,   'X-8',          32, 7],
            // Grade 11 — will hold students now in grade 12 (same major)
            [11, $majorIPA->id,    'XI IPA 1',     34, 8],
            [11, $majorIPA->id,    'XI IPA 2',     34, 9],
            [11, $majorIPA->id,    'XI IPA 3',     34, 10],
            [11, $majorIPA->id,    'XI IPA 4',     32, 11],
            [11, $majorIPS->id,    'XI IPS 1',     32, 12],
            [11, $majorIPS->id,    'XI IPS 2',     32, 13],
            [11, $majorIPS->id,    'XI IPS 3',     30, 14],
            [11, $majorBahasa->id, 'XI Bahasa 1',  28, 15],
            // Grade 12 (alumni — new students, graduated in 2024/2025)
            [12, $majorIPA->id,    'XII IPA 1',    70, 16],
            [12, $majorIPA->id,    'XII IPA 2',    68, 17],
            [12, $majorIPA->id,    'XII IPA 3',    66, 18],
            [12, $majorIPA->id,    'XII IPA 4',    64, 19],
            [12, $majorIPS->id,    'XII IPS 1',    62, 20],
            [12, $majorIPS->id,    'XII IPS 2',    62, 21],
            [12, $majorIPS->id,    'XII IPS 3',    60, 22],
            [12, $majorBahasa->id, 'XII Bahasa 1', 58, 23],
        ];

        $historicalClasses = []; // name => SchoolClass
        foreach ($classDefs as [$grade, $majorId, $name, $capacity, $tIdx]) {
            $cls = SchoolClass::updateOrCreate(
                ['academic_year_id' => $prevYear->id, 'name' => $name],
                [
                    'major_id'            => $majorId,
                    'grade_level'         => $grade,
                    'capacity'            => $capacity,
                    'homeroom_teacher_id' => $eligibleTeachers[$tIdx],
                    'is_active'           => false,
                ]
            );
            $historicalClasses[$name] = $cls;
        }

        $this->command->info('✓ 24 historical classes for 2024/2025 created.');

        // ----------------------------------------------------------------
        // 5. Current 2025/2026 academic year & its classes
        // ----------------------------------------------------------------
        $currYear    = AcademicYear::where('name', '2025/2026')->firstOrFail();
        $currClasses = SchoolClass::where('academic_year_id', $currYear->id)
            ->orderBy('id')
            ->get();

        // Separate by grade
        $grade11Classes = $currClasses->where('grade_level', 11)->sortBy('name')->values();
        $grade12Classes = $currClasses->where('grade_level', 12)->sortBy('name')->values();

        // ----------------------------------------------------------------
        // 6. class_student_history for current grade-11 → historical X classes
        //    Distribute 184 students across 8 X classes
        // ----------------------------------------------------------------
        // Collect all grade-11 student IDs, ordered by current class name then student id
        $grade11StudentIds = [];
        foreach ($grade11Classes as $cls) {
            $ids = DB::table('students')
                ->where('class_id', $cls->id)
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            $grade11StudentIds = array_merge($grade11StudentIds, $ids);
        }

        $xClassNames  = ['X-1','X-2','X-3','X-4','X-5','X-6','X-7','X-8'];
        $xClassIds    = array_map(function ($n) use ($historicalClasses) {
            return $historicalClasses[$n]->id;
        }, $xClassNames);
        $totalGrade11 = count($grade11StudentIds);
        $xPerClass    = (int) ceil($totalGrade11 / 8);
        $histRows     = [];

        foreach ($grade11StudentIds as $i => $sid) {
            $xIdx       = min((int) ($i / $xPerClass), 7);
            $histRows[] = [
                'student_id'       => $sid,
                'class_id'         => $xClassIds[$xIdx],
                'academic_year_id' => $prevYear->id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        foreach (array_chunk($histRows, 200) as $chunk) {
            DB::table('class_student_history')->insertOrIgnore($chunk);
        }

        $this->command->info("✓ {$totalGrade11} grade-11 students mapped to historical X classes.");

        // ----------------------------------------------------------------
        // 7. class_student_history for current grade-12 → historical XI classes
        //    Direct mapping: XII IPA 1 → XI IPA 1, XII IPS 1 → XI IPS 1, etc.
        // ----------------------------------------------------------------
        $grade12HistMap = [
            'XII IPA 1'    => 'XI IPA 1',
            'XII IPA 2'    => 'XI IPA 2',
            'XII IPA 3'    => 'XI IPA 3',
            'XII IPA 4'    => 'XI IPA 4',
            'XII IPS 1'    => 'XI IPS 1',
            'XII IPS 2'    => 'XI IPS 2',
            'XII IPS 3'    => 'XI IPS 3',
            'XII Bahasa 1' => 'XI Bahasa 1',
        ];

        $histRows12     = [];
        $totalGrade12   = 0;
        foreach ($grade12Classes as $cls12) {
            $histClsName = $grade12HistMap[$cls12->name] ?? null;
            if (!$histClsName || !isset($historicalClasses[$histClsName])) {
                continue;
            }
            $histCls12Id = $historicalClasses[$histClsName]->id;
            $ids12 = DB::table('students')
                ->where('class_id', $cls12->id)
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            foreach ($ids12 as $sid) {
                $histRows12[] = [
                    'student_id'       => $sid,
                    'class_id'         => $histCls12Id,
                    'academic_year_id' => $prevYear->id,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
                $totalGrade12++;
            }
        }

        foreach (array_chunk($histRows12, 200) as $chunk) {
            DB::table('class_student_history')->insertOrIgnore($chunk);
        }

        $this->command->info("✓ {$totalGrade12} grade-12 students mapped to historical XI classes.");

        // ----------------------------------------------------------------
        // 8. class_student_history for 2025/2026 current year (snapshot)
        // ----------------------------------------------------------------
        $allCurrentStudents = DB::table('students')
            ->where('is_active', true)
            ->whereNotNull('class_id')
            ->select('id', 'class_id')
            ->get();

        $currHistRows = [];
        foreach ($allCurrentStudents as $s) {
            $currHistRows[] = [
                'student_id'       => $s->id,
                'class_id'         => $s->class_id,
                'academic_year_id' => $currYear->id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        foreach (array_chunk($currHistRows, 200) as $chunk) {
            DB::table('class_student_history')->insertOrIgnore($chunk);
        }

        $this->command->info('✓ Current year (2025/2026) class_student_history snapshot created.');

        // ----------------------------------------------------------------
        // 9. Create 510 alumni students for historical XII classes
        // ----------------------------------------------------------------
        $alumniClassDefs = [
            'XII IPA 1'    => 68,
            'XII IPA 2'    => 65,
            'XII IPA 3'    => 63,
            'XII IPA 4'    => 60,
            'XII IPS 1'    => 62,
            'XII IPS 2'    => 60,
            'XII IPS 3'    => 58,
            'XII Bahasa 1' => 54,
        ];
        // Total = 510

        $parentIds  = DB::table('parents')->orderBy('id')->pluck('id')->toArray();
        $siswaRole  = DB::table('roles')->where('name', 'siswa')->value('id');

        $maleFirstNames = [
            'Adi','Agung','Ahmad','Aldi','Alif','Andika','Arif','Aryo',
            'Bagas','Bagus','Bima','Dafa','Danu','Dimas','Eko','Fajar',
            'Farhan','Fikri','Galang','Gilang','Hafiz','Ilham','Ivan',
            'Kevin','Krisna','Luthfi','Mahendra','Malik','Nanda','Pandu',
            'Putra','Rafi','Rahmat','Rangga','Reza','Rifki','Rio',
            'Rizki','Rizal','Satria','Tegar','Wahyu','Yogi','Yusuf',
            'Zaidan','Zaki','Dandi','Brian','Novian','Vino',
        ];
        $femaleFirstNames = [
            'Adinda','Alya','Amanda','Amelia','Anisa','Ayu','Bunga',
            'Cahya','Cindy','Dea','Desi','Dewi','Diana','Dinda','Elsa',
            'Farah','Fatimah','Fitri','Ghina','Hana','Indah','Intan',
            'Julia','Karina','Laila','Lathifa','Maya','Mega','Nabila',
            'Nadia','Nafisa','Nisa','Novita','Nurul','Putri','Rini',
            'Rizka','Rosa','Salsabila','Sari','Sinta','Siti','Tiara',
            'Ulfa','Vina','Wulan','Yuli','Zahra','Ristia','Okta',
        ];
        $lastNames = [
            'Santoso','Wijaya','Kusuma','Saputra','Rahayu','Hidayat',
            'Purnomo','Wibowo','Susanto','Prasetyo','Hartono','Nugroho',
            'Setiawan','Gunawan','Firmansyah','Ramadhan','Budiman','Mulyono',
            'Sulistyo','Putra','Nugraha','Mahendra','Maulana','Fauzi',
            'Wahyudi','Kusumawati','Lestari','Andriani','Permata','Azzahra',
        ];
        $birthPlaces = ['Bogor','Depok','Bekasi','Tangerang','Jakarta','Bandung','Sukabumi','Cianjur'];
        $streets = [
            'Jl. Raya Tajurhalang','Jl. Ciseeng','Jl. Parung','Jl. Ciampea',
            'Jl. Leuwiliang','Jl. Ciawi','Jl. Bojonggede','Jl. Citayam',
        ];
        $kelurahans = ['Tajurhalang','Ciseeng','Parung','Gunung Sindur','Bojonggede'];

        $nisCounter    = 1;       // NIS for tahun_masuk 2022
        $nisnBase      = 22345000;
        $parentIdx     = 0;
        $tahunMasuk    = 2022;    // Alumni entered in 2022

        $alumniUsers    = [];
        $alumniStudents = [];
        $emailsUsed     = [];
        $alumniClassAssignments = []; // student_index => class_id (historical XII)

        foreach ($alumniClassDefs as $clsName => $count) {
            $histClsId = $historicalClasses[$clsName]->id ?? null;
            if (!$histClsId) {
                continue;
            }

            for ($s = 0; $s < $count; $s++) {
                $gender    = ($s % 2 === 0) ? 'L' : 'P';
                $firstName = $gender === 'L'
                    ? $faker->randomElement($maleFirstNames)
                    : $faker->randomElement($femaleFirstNames);
                $lastName  = $faker->randomElement($lastNames);
                $name      = "{$firstName} {$lastName}";

                $nis       = (string) $tahunMasuk . str_pad($nisCounter++, 4, '0', STR_PAD_LEFT);
                $nisn      = str_pad($nisnBase++, 10, '0', STR_PAD_LEFT);
                $barcodeId = 'SMAN1TJR-' . $nis;

                $ageYears  = $faker->numberBetween(18, 20); // graduated ~age 18-20
                $birthYear = 2026 - $ageYears;
                $bDate     = sprintf('%04d-%02d-%02d',
                    $birthYear,
                    $faker->numberBetween(1, 12),
                    $faker->numberBetween(1, 28)
                );

                $address = $faker->randomElement($streets) . ' No. '
                    . $faker->numberBetween(1, 200)
                    . ', Kel. ' . $faker->randomElement($kelurahans) . ', Kab. Bogor';

                $email = "alumni.{$nis}@sman1tajurhalang.sch.id";
                if (in_array($email, $emailsUsed)) {
                    $email = "alumni.{$nis}x@sman1tajurhalang.sch.id";
                }
                $emailsUsed[] = $email;

                $parentId = $parentIds[$parentIdx % count($parentIds)];
                $parentIdx++;

                $alumniUsers[] = [
                    'name'       => $name,
                    'email'      => $email,
                    'password'   => $password,
                    'is_active'  => false,  // alumni = not active user
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $alumniStudents[] = [
                    'parent_id'   => $parentId,
                    'class_id'    => null,    // no current class (alumni)
                    'nis'         => $nis,
                    'nisn'        => $nisn,
                    'name'        => $name,
                    'gender'      => $gender,
                    'phone'       => null,
                    'birth_date'  => $bDate,
                    'birth_place' => $faker->randomElement($birthPlaces),
                    'address'     => $address,
                    'barcode_id'  => $barcodeId,
                    'is_active'   => false,  // alumni
                    'tahun_masuk' => $tahunMasuk,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    '_hist_cls'   => $histClsId, // temp
                ];
            }
        }

        $totalAlumni = count($alumniUsers);

        // Insert user accounts in chunks
        foreach (array_chunk($alumniUsers, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Fetch the just-inserted user IDs
        $alumniUserIds = array_reverse(
            DB::table('users')
                ->orderBy('id', 'desc')
                ->take($totalAlumni)
                ->pluck('id')
                ->toArray()
        );

        // Assign siswa role
        if ($siswaRole) {
            $roleRows = [];
            foreach ($alumniUserIds as $uid) {
                $roleRows[] = [
                    'role_id'    => $siswaRole,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $uid,
                ];
            }
            foreach (array_chunk($roleRows, 200) as $chunk) {
                DB::table('model_has_roles')->insertOrIgnore($chunk);
            }
        }

        // Insert alumni students + collect IDs for class_student_history
        $alumniHistRows = [];
        foreach ($alumniStudents as $idx => &$alumniStu) {
            $histClsId = $alumniStu['_hist_cls'];
            unset($alumniStu['_hist_cls']);
            $alumniStu['user_id'] = $alumniUserIds[$idx] ?? null;

            // We'll bulk-insert and then fetch IDs for history
            $alumniHistRows[$idx] = $histClsId;
        }
        unset($alumniStu);

        foreach (array_chunk($alumniStudents, 100) as $chunk) {
            DB::table('students')->insert($chunk);
        }

        // Fetch alumni student IDs
        $alumniStudentIds = array_reverse(
            DB::table('students')
                ->orderBy('id', 'desc')
                ->take($totalAlumni)
                ->pluck('id')
                ->toArray()
        );

        // Insert class_student_history for alumni
        $histAlumniRows = [];
        foreach ($alumniStudentIds as $idx => $sid) {
            $histAlumniRows[] = [
                'student_id'       => $sid,
                'class_id'         => $alumniHistRows[$idx],
                'academic_year_id' => $prevYear->id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        foreach (array_chunk($histAlumniRows, 200) as $chunk) {
            DB::table('class_student_history')->insertOrIgnore($chunk);
        }

        $this->command->info("✓ {$totalAlumni} alumni students created + mapped to historical XII classes.");
        $this->command->info('PreviousYearDataSeeder complete.');
    }
}