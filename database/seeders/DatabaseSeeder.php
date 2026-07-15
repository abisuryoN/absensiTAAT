<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run foundational seeders
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            WhatsappTemplateSeeder::class,
            SchoolProfileSeeder::class,
        ]);

        // 2. Create Active Academic Year and Semester
        $academicYear = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'is_active' => true,
            ]
        );

        $semester = Semester::updateOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'semester_number' => 1,
            ],
            [
                'name' => 'Ganjil',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'is_active' => true,
            ]
        );

        // 3. Create Super Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@sman1tajurhalang.sch.id'],
            [
                'name' => 'Operator TU SMAN 1 Tajurhalang',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');

        $this->command->info('Active Academic Year 2025/2026 & Semester Ganjil created.');
        $this->command->info('Default Super Admin created: admin@sman1tajurhalang.sch.id / password');
    }
}
