<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create 'guru_piket' role - a shared account role for gate duty teachers
        Role::firstOrCreate(['name' => 'guru_piket', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Role::where('name', 'guru_piket')->where('guard_name', 'web')->delete();
    }
};