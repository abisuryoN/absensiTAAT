<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    /**
     * Generate ~494 orang tua/wali dengan akun login.
     * Semua password: password123
     *
     * Proporsi: 90% siswa punya orang tua unik, 10% share (kakak-adik).
     * ParentSeeder hanya membuat 494 parent records — StudentSeeder yang
     * mengatur assignment ke siswa dengan rasio 90/10.
     */
    public function run(): void
    {
        $faker    = Faker::create('id_ID');
        $password = Hash::make('password123');
        $now      = now()->toDateTimeString();

        // Kode wilayah Bogor/Depok area untuk NIK
        $kodWilayah = ['320112', '320114', '320116', '320118', '320301', '320305', '327305', '327401'];

        // Nama belakang umum untuk slug email
        $slugUsed = [];

        $usersToInsert   = [];
        $parentsToInsert = [];

        for ($i = 1; $i <= 494; $i++) {
            $gender = $faker->randomElement(['L', 'P']);

            // Generate nama Indonesia realistis
            if ($gender === 'L') {
                $firstName = $faker->randomElement([
                    'Ahmad', 'Budi', 'Slamet', 'Agus', 'Wahyu', 'Dedi', 'Eko', 'Fajar',
                    'Gunawan', 'Hendra', 'Irwan', 'Joko', 'Kuat', 'Lukman', 'Maman',
                    'Nanda', 'Oki', 'Panji', 'Rudi', 'Sigit', 'Taufik', 'Umar', 'Veri',
                    'Wawan', 'Yudi', 'Zaenal', 'Bambang', 'Dani', 'Fandi', 'Gatot',
                    'Haris', 'Ivan', 'Kurnia', 'Lutfi', 'Mulyadi', 'Nanang', 'Oki',
                    'Prayogo', 'Rahmad', 'Surya', 'Teguh', 'Ucok', 'Wahid', 'Yanto',
                ]);
            } else {
                $firstName = $faker->randomElement([
                    'Siti', 'Dewi', 'Rina', 'Nurul', 'Sri', 'Yuli', 'Lia', 'Fitri',
                    'Heni', 'Indah', 'Juwita', 'Kartika', 'Lestari', 'Mira', 'Nini',
                    'Okta', 'Putri', 'Rini', 'Suci', 'Tina', 'Umy', 'Vina', 'Wati',
                    'Yanti', 'Zulfa', 'Aini', 'Bella', 'Citra', 'Desi', 'Erna',
                    'Farida', 'Gita', 'Hasna', 'Ika', 'Juliana', 'Karina', 'Laila',
                    'Murni', 'Nanda', 'Opit', 'Prita', 'Ratna', 'Sari', 'Tutik',
                ]);
            }

            $lastName = $faker->randomElement([
                'Santoso', 'Wijaya', 'Sumarno', 'Kusuma', 'Saputra', 'Rahayu', 'Hidayat',
                'Purnomo', 'Wibowo', 'Sari', 'Susanto', 'Prasetyo', 'Hartono', 'Wahyudi',
                'Nugroho', 'Andriani', 'Budiman', 'Sukamto', 'Firmansyah', 'Ramadhan',
                'Setiawan', 'Gunawan', 'Mahmud', 'Ismail', 'Yusuf', 'Hasan', 'Salim',
                'Basuki', 'Subagyo', 'Mulyono', 'Suprapto', 'Suhartono', 'Kuswanto',
                'Triyono', 'Sulistyo', 'Wardoyo', 'Purwanto', 'Rohmad', 'Sugiarto',
            ]);

            $name = "{$firstName} {$lastName}";

            // Slug unik untuk email
            $baseSlug = strtolower(str_replace(' ', '.', "{$firstName}.{$lastName}"));
            $slug     = $baseSlug;
            $counter  = 1;
            while (in_array($slug, $slugUsed)) {
                $slug = $baseSlug . $counter;
                $counter++;
            }
            $slugUsed[] = $slug;

            $email = "ortu.{$slug}@gmail.com";

            // Tanggal lahir orang tua: usia 35-55 tahun
            $birthYear  = $faker->numberBetween(1969, 1990);
            $birthMonth = str_pad($faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
            $birthDay   = str_pad($faker->numberBetween(1, 28), 2, '0', STR_PAD_LEFT);

            // NIK: [kode_wilayah 6 digit][tgl_lahir 6 digit: DDMMYY perempuan +40][nomor_urut 4 digit]
            $kodeWil = $faker->randomElement($kodWilayah);
            $ddMmYy  = $gender === 'P'
                ? str_pad((int)$birthDay + 40, 2, '0', STR_PAD_LEFT) . $birthMonth . substr($birthYear, 2)
                : $birthDay . $birthMonth . substr($birthYear, 2);
            $nik = $kodeWil . $ddMmYy . str_pad($i, 4, '0', STR_PAD_LEFT);

            // Phone
            $phonePrefix = $faker->randomElement(['0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853', '0857', '0858', '0877', '0878']);
            $phone       = $phonePrefix . $faker->numerify('########');
            $phoneSecondary = $faker->optional(0.6)->numerify('0812########');

            // Alamat (area Bogor/Depok/Tajurhalang)
            $streets  = ['Jl. Raya Tajurhalang', 'Jl. Ciseeng', 'Jl. Parung', 'Jl. Ciampea', 'Jl. Leuwiliang', 'Jl. Ciawi', 'Jl. Bojonggede', 'Jl. Citayam', 'Jl. Raya Parung', 'Jl. Sawangan', 'Jl. Ciputat Raya', 'Perum Griya Tajurhalang', 'Jl. Gunung Sindur'];
            $kelurahans = ['Tajurhalang', 'Ciseeng', 'Parung', 'Gunung Sindur', 'Bojonggede', 'Citayam', 'Sawangan', 'Ciampea', 'Leuwiliang'];
            $address = $faker->randomElement($streets) . ' No. ' . $faker->numberBetween(1, 120) . ', Kel. ' . $faker->randomElement($kelurahans) . ', Kab. Bogor';

            $usersToInsert[] = [
                'name'       => $name,
                'email'      => $email,
                'password'   => $password,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $parentsToInsert[] = [
                'name'            => $name,
                'nik'             => $nik,
                'phone'           => $phone,
                'phone_secondary' => $phoneSecondary,
                'relationship'    => 'Wali',
                'address'         => $address,
                'email'           => $email,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        // Bulk insert users in chunks, then get IDs
        $chunkSize = 50;
        $userChunks = array_chunk($usersToInsert, $chunkSize);

        foreach ($userChunks as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Get inserted user IDs (the last 494 users by ID, in insertion order)
        $userIds = array_reverse(
            DB::table('users')
                ->orderBy('id', 'desc')
                ->take(494)
                ->pluck('id')
                ->toArray()
        );

        // Assign parent role in bulk
        $siswaRoleId = DB::table('roles')->where('name', 'parent')->value('id');
        if ($siswaRoleId) {
            $roleAssignments = array_map(fn($uid) => [
                'role_id'    => $siswaRoleId,
                'model_type' => 'App\\Models\\User',
                'model_id'   => $uid,
            ], $userIds);
            foreach (array_chunk($roleAssignments, $chunkSize) as $chunk) {
                DB::table('model_has_roles')->insertOrIgnore($chunk);
            }
        }

        // Attach user_id to parentsToInsert
        foreach ($parentsToInsert as $idx => &$parent) {
            $parent['user_id'] = $userIds[$idx] ?? null;
        }
        unset($parent);

        // Insert a few nonaktif parents for filter testing
        $parentsToInsert[490]['is_active'] = false;
        $parentsToInsert[491]['is_active'] = false;
        $parentsToInsert[492]['is_active'] = false;

        // Bulk insert parents
        foreach (array_chunk($parentsToInsert, $chunkSize) as $chunk) {
            DB::table('parents')->insert($chunk);
        }

        $this->command->info('✓ 494 Parent records + user accounts created (3 nonaktif for testing).');
    }
}