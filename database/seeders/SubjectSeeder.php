<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Matematika',                                    'code' => 'MTK',   'description' => 'Matematika Wajib'],
            ['name' => 'Bahasa Indonesia',                              'code' => 'BIN',   'description' => 'Bahasa Indonesia'],
            ['name' => 'Bahasa Inggris',                                'code' => 'BING',  'description' => 'Bahasa Inggris'],
            ['name' => 'Fisika',                                        'code' => 'FIS',   'description' => 'Fisika — Peminatan IPA'],
            ['name' => 'Kimia',                                         'code' => 'KIM',   'description' => 'Kimia — Peminatan IPA'],
            ['name' => 'Biologi',                                       'code' => 'BIO',   'description' => 'Biologi — Peminatan IPA'],
            ['name' => 'Sejarah Indonesia',                             'code' => 'SEJ',   'description' => 'Sejarah Indonesia Wajib'],
            ['name' => 'Geografi',                                      'code' => 'GEO',   'description' => 'Geografi — Peminatan IPS'],
            ['name' => 'Ekonomi',                                       'code' => 'EKO',   'description' => 'Ekonomi — Peminatan IPS'],
            ['name' => 'Sosiologi',                                     'code' => 'SOS',   'description' => 'Sosiologi — Peminatan IPS'],
            ['name' => 'Pendidikan Pancasila dan Kewarganegaraan',      'code' => 'PPKN',  'description' => 'PPKn'],
            ['name' => 'Pendidikan Agama Islam dan Budi Pekerti',       'code' => 'PAI',   'description' => 'Pendidikan Agama Islam'],
            ['name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',  'code' => 'PJOK',  'description' => 'PJOK'],
            ['name' => 'Seni Budaya',                                   'code' => 'SBD',   'description' => 'Seni Budaya'],
            ['name' => 'Bahasa Jepang',                                 'code' => 'BJP',   'description' => 'Bahasa Jepang — Lintas Minat / Bahasa'],
            ['name' => 'Bahasa Arab',                                   'code' => 'BARAB', 'description' => 'Bahasa Arab — Lintas Minat / Bahasa'],
            ['name' => 'Teknologi Informasi dan Komunikasi',            'code' => 'TIK',   'description' => 'TIK / Informatika'],
        ];

        foreach ($subjects as $data) {
            Subject::updateOrCreate(['code' => $data['code']], array_merge($data, ['is_active' => true]));
        }

        $this->command->info('✓ ' . count($subjects) . ' Subjects created.');
    }
}