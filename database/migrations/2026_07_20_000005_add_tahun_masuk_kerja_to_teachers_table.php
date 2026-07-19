<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun_masuk_kerja')->nullable()->after('is_active')
                ->comment('Tahun masuk kerja guru, digunakan untuk generate password default');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('tahun_masuk_kerja');
        });
    }
};