<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolProfile;

class SchoolProfileSeeder extends Seeder
{
    public function run(): void
    {
        SchoolProfile::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'SMAN 1 Tajurhalang',
                'npsn' => '20231362',
                'address' => 'Jl. Tajurhalang No. 1, Kec. Tajurhalang, Kab. Bogor, Jawa Barat 16320',
                'phone' => '021-87981234',
                'email' => 'info@sman1tajurhalang.sch.id',
                'website' => 'https://sman1tajurhalang.sch.id',
                'principal_name' => 'Dr. H. Mulyana, M.Pd.',
                'principal_nip' => '197105121998021003',
                'description' => 'Sekolah Menengah Atas Negeri 1 Tajurhalang, Kabupaten Bogor',
            ]
        );
    }
}
