<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $admins = [
            [
                'name'  => 'Kepala Sekolah SMAN 1 Tajurhalang',
                'email' => 'superadmin1@sman1tajurhalang.sch.id',
            ],
            [
                'name'  => 'Operator TU SMAN 1 Tajurhalang',
                'email' => 'superadmin2@sman1tajurhalang.sch.id',
            ],
        ];

        foreach ($admins as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => $password,
                    'is_active' => true,
                ]
            );
            $user->syncRoles(['super_admin']);
        }

        $this->command->info('✓ 2 Super Admin accounts created.');
    }
}