<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            // NIK: required unique identifier
            $table->string('nik', 20)->unique()->after('name');
            // Email for login portal (nullable until admin assigns)
            $table->string('email', 100)->nullable()->unique()->after('address');
            // Active status toggle
            $table->boolean('is_active')->default(true)->after('email');

            // Make phone nullable (nik is now the primary unique key)
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn('nik');
            $table->dropUnique(['email']);
            $table->dropColumn('email');
            $table->dropColumn('is_active');
            $table->string('phone', 20)->nullable(false)->change();
        });
    }
};