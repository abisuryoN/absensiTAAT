<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create 'parent' role if it doesn't exist
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        Role::where('name', 'parent')->where('guard_name', 'web')->delete();
    }
};