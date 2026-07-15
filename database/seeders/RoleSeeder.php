<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Master Data
            'manage-master-data',
            'view-master-data',
            
            // Attendance Gate
            'scan-attendance-gate',
            'view-attendance-gate',
            'edit-attendance-gate',
            
            // Attendance Subject
            'manage-attendance-subject',
            'view-attendance-subject',
            
            // Reports
            'view-reports',
            'export-reports',
            
            // Import/Export
            'import-data',
            'export-data',
            
            // Settings & Audit
            'manage-settings',
            'view-activity-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Roles and assign permissions
        $superAdmin = Role::findOrCreate('super_admin');
        $superAdmin->givePermissionTo(Permission::all());

        $guru = Role::findOrCreate('guru');
        $guru->givePermissionTo([
            'manage-attendance-subject',
            'view-attendance-subject',
            'view-reports',
        ]);

        $siswa = Role::findOrCreate('siswa');
        $siswa->givePermissionTo([
            'view-reports', // self reports
        ]);
    }
}
