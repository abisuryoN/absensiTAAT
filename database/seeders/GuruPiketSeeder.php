<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruPiketSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'piket@sman1tajurhalang.sch.id'],
            [
                'name'      => 'Guru Piket SMAN 1 Tajurhalang',
                'password'  => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $user->syncRoles(['guru_piket']);

        $this->command->info('✓ Guru Piket shared account created: piket@sman1tajurhalang.sch.id');
    }
}