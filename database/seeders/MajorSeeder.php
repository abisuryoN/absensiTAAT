<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $majors = [
            [
                'name'        => 'Umum',
                'code'        => 'UMUM',
                'description' => 'Kelas X — belum ada penjurusan',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ilmu Pengetahuan Alam',
                'code'        => 'IPA',
                'description' => 'Peminatan Matematika dan Ilmu Pengetahuan Alam',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ilmu Pengetahuan Sosial',
                'code'        => 'IPS',
                'description' => 'Peminatan Ilmu Pengetahuan Sosial',
                'is_active'   => true,
            ],
            [
                'name'        => 'Bahasa dan Budaya',
                'code'        => 'BAHASA',
                'description' => 'Peminatan Bahasa dan Budaya',
                'is_active'   => true,
            ],
        ];

        foreach ($majors as $data) {
            Major::updateOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('✓ 4 Majors created (UMUM, IPA, IPS, BAHASA).');
    }
}