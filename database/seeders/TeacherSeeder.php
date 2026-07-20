<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * 28 guru dengan nama Indonesia realistis.
     * Teacher[0-23] akan menjadi wali kelas untuk 24 kelas (diassign di ClassSeeder).
     * Teacher[24-27] adalah guru bidang studi tambahan.
     * Semua password: password123
     */
    public function run(): void
    {
        $password = Hash::make('password123');
        $now      = now();

        // -----------------------------------------------------------------------
        // Static teacher data — NIP format: YYYYMMYYYYMMGSSS
        //   YYYYMM = tgl lahir, YYYYMM = tgl masuk, G = gender (1=L,2=P), SSS = urut
        // -----------------------------------------------------------------------
        $teachers = [
            // [name, slug, nip, nuptk, gender, phone, tahun_masuk_kerja, address]
            ['Budi Santoso, S.Pd.',         'budi.santoso',    '197803152005011001', '1234567890123401', 'L', '081234567801', 2005, 'Jl. Raya Tajurhalang No. 15, Bogor'],
            ['Siti Rahayu, S.Pd.',          'siti.rahayu',     '197912202006022002', '1234567890123402', 'P', '081234567802', 2006, 'Jl. Ciseeng Raya No. 8, Bogor'],
            ['Ahmad Fauzi, M.Pd.',          'ahmad.fauzi',     '198104102007011003', '1234567890123403', 'L', '081234567803', 2007, 'Jl. Parung No. 22, Bogor'],
            ['Dewi Kusuma, S.Pd.',          'dewi.kusuma',     '198207252008022004', '1234567890123404', 'P', '081234567804', 2008, 'Jl. Raya Ciputat No. 5, Tangerang Selatan'],
            ['Hendra Saputra, S.Pd.',       'hendra.saputra',  '197605182009011005', '1234567890123405', 'L', '081234567805', 2009, 'Jl. Tajurhalang Indah No. 3, Bogor'],
            ['Nurul Hidayah, S.Pd.',        'nurul.hidayah',   '198309302009022006', '1234567890123406', 'P', '081234567806', 2009, 'Perum Griya Tajurhalang Blok B2, Bogor'],
            ['Joko Prasetyo, M.Si.',        'joko.prasetyo',   '197711082010011007', '1234567890123407', 'L', '081234567807', 2010, 'Jl. Sawangan No. 45, Depok'],
            ['Sri Wahyuni, S.Pd.',          'sri.wahyuni',     '198502142011022008', '1234567890123408', 'P', '081234567808', 2011, 'Jl. Ciampea No. 17, Bogor'],
            ['Bambang Sumarno, S.Pd.',      'bambang.sumarno', '197809062011011009', '1234567890123409', 'L', '081234567809', 2011, 'Jl. Leuwiliang No. 9, Bogor'],
            ['Rina Astuti, S.Pd.I.',        'rina.astuti',     '198406172012022010', '1234567890123410', 'P', '081234567810', 2012, 'Jl. Raya Parung No. 31, Bogor'],
            ['Wahyu Hidayat, S.Pd.',        'wahyu.hidayat',   '198001252012011011', '1234567890123411', 'L', '081234567811', 2012, 'Jl. Pondok Cabe No. 7, Tangerang Selatan'],
            ['Fitri Handayani, S.Sn.',      'fitri.handayani', '198708032013022012', '1234567890123412', 'P', '081234567812', 2013, 'Perum Taman Tajurhalang No. 14, Bogor'],
            ['Agus Supriyanto, S.Pd.',      'agus.supriyanto', '198211192013011013', '1234567890123413', 'L', '081234567813', 2013, 'Jl. Gunung Sindur No. 6, Bogor'],
            ['Yuli Setiawati, S.Pd.',       'yuli.setiawati',  '198904082014022014', '1234567890123414', 'P', '081234567814', 2014, 'Jl. Raya Depok No. 19, Depok'],
            ['Dian Pratiwi, M.Sc.',         'dian.pratiwi',    '199001132015022015', '1234567890123415', 'P', '081234567815', 2015, 'Jl. Cibinong No. 33, Bogor'],
            ['Sigit Wibowo, S.Pd.',         'sigit.wibowo',    '198812022015011016', '1234567890123416', 'L', '081234567816', 2015, 'Jl. Raya Tajurhalang No. 42, Bogor'],
            ['Endah Sulistyowati, M.Pd.',   'endah.sulistyo',  '197706162016022017', '1234567890123417', 'P', '081234567817', 2016, 'Jl. Bojonggede No. 11, Bogor'],
            ['Eko Purnomo, S.Pd.',          'eko.purnomo',     '198503292016011018', '1234567890123418', 'L', '081234567818', 2016, 'Jl. Citayam No. 25, Depok'],
            ['Lia Kusumawati, S.Pd.',       'lia.kusumawati',  '199105172017022019', '1234567890123419', 'P', '081234567819', 2017, 'Jl. Tajurhalang Utama No. 8, Bogor'],
            ['Rudi Hartono, S.E., S.Pd.',   'rudi.hartono',    '198710262017011020', '1234567890123420', 'L', '081234567820', 2017, 'Jl. Ciawi No. 16, Bogor'],
            ['Heni Ratnasari, S.Pd.',       'heni.ratnasari',  '199208032018022021', '1234567890123421', 'P', '081234567821', 2018, 'Perum Pamulang Permai No. 4, Tangerang Selatan'],
            ['Darman Purwanto, S.Pd.',      'darman.purwanto', '198614102018011022', '1234567890123422', 'L', '081234567822', 2018, 'Jl. Raya Parung Panjang No. 37, Bogor'],
            ['Suci Indrawati, S.Pd.',       'suci.indrawati',  '199311252019022023', '1234567890123423', 'P', '081234567823', 2019, 'Jl. Ciputat Raya No. 12, Tangerang Selatan'],
            ['Taufik Hidayat, S.Pd.I.',     'taufik.hidayat',  '199007142019011024', '1234567890123424', 'L', '081234567824', 2019, 'Jl. Raya Tajurhalang No. 56, Bogor'],
            // Guru tambahan (bukan wali kelas)
            ['Maya Sari, S.Sn.',            'maya.sari',       '199402182020022025', '1234567890123425', 'P', '081234567825', 2020, 'Jl. Bogor Raya No. 28, Bogor'],
            ['Irwan Santoso, S.Pd.',        'irwan.santoso',   '199115092020011026', '1234567890123426', 'L', '081234567826', 2020, 'Jl. Raya Serpong No. 9, Tangerang Selatan'],
            ['Lestari Wulandari, S.Pd.',    'lestari.wulandari','199507212021022027','1234567890123427', 'P', '081234567827', 2021, 'Jl. Tajurhalang Selatan No. 3, Bogor'],
            ['Gunawan Setiadi, M.Hum.',     'gunawan.setiadi', '198816032022011028', '1234567890123428', 'L', '081234567828', 2022, 'Jl. Pamulang No. 19, Tangerang Selatan'],
        ];

        foreach ($teachers as $index => $t) {
            [$name, $slug, $nip, $nuptk, $gender, $phone, $tahunMasuk, $address] = $t;

            $email = "guru.{$slug}@sman1tajurhalang.sch.id";

            // Create / update User account
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'      => $name,
                    'password'  => $password,
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['guru']);

            // Create / update Teacher record
            Teacher::updateOrCreate(
                ['nip' => $nip],
                [
                    'user_id'          => $user->id,
                    'nip'              => $nip,
                    'nuptk'            => $nuptk,
                    'name'             => $name,
                    'phone'            => $phone,
                    'gender'           => $gender,
                    'address'          => $address,
                    'is_active'        => true,
                    'tahun_masuk_kerja'=> $tahunMasuk,
                ]
            );
        }

        // Assign non-active teacher as sample nonaktif (testing filter)
        Teacher::where('nip', '198816032022011028')->update(['is_active' => false]);

        $this->command->info('✓ ' . count($teachers) . ' Teachers created (1 set nonaktif for testing).');
    }
}