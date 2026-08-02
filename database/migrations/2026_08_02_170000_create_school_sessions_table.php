<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // school_sessions — dynamic session templates (Pagi, Siang, etc.)
        // ---------------------------------------------------------------
        Schema::create('school_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->time('gate_open_time');
            $table->time('school_start_time');
            $table->unsignedSmallInteger('late_threshold_minutes')->default(15);
            $table->time('gate_close_time');
            $table->time('auto_alpha_time');
            $table->time('school_end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ---------------------------------------------------------------
        // grade_session_settings — maps grade level → session per tahun ajaran
        // ---------------------------------------------------------------
        Schema::create('grade_session_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('grade_level');  // 10, 11, 12
            $table->foreignId('school_session_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'grade_level'], 'grade_session_unique');
            $table->index('academic_year_id');
        });

        // ---------------------------------------------------------------
        // classes — add nullable session override column
        // ---------------------------------------------------------------
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('school_session_id')
                ->nullable()
                ->after('is_active')
                ->constrained('school_sessions')
                ->nullOnDelete();
        });

        // ---------------------------------------------------------------
        // settings — seed simulation & multi-session keys
        // ---------------------------------------------------------------
        $now = now();
        $seeds = [
            [
                'group'       => 'simulation',
                'key'         => 'simulation_enabled',
                'value'       => 'false',
                'type'        => 'boolean',
                'description' => 'Aktifkan mode simulasi tanggal & waktu',
            ],
            [
                'group'       => 'simulation',
                'key'         => 'simulation_date',
                'value'       => null,
                'type'        => 'string',
                'description' => 'Tanggal simulasi (Y-m-d)',
            ],
            [
                'group'       => 'simulation',
                'key'         => 'simulation_time',
                'value'       => null,
                'type'        => 'string',
                'description' => 'Jam simulasi (H:i)',
            ],
            [
                'group'       => 'simulation',
                'key'         => 'simulation_day_override',
                'value'       => 'Automatic',
                'type'        => 'string',
                'description' => 'Override hari (Automatic | Senin | Selasa | ... | Minggu)',
            ],
            [
                'group'       => 'session',
                'key'         => 'multiple_sessions_enabled',
                'value'       => 'false',
                'type'        => 'boolean',
                'description' => 'Aktifkan sistem multi-sesi sekolah',
            ],
            [
                'group'       => 'session',
                'key'         => 'default_school_session_id',
                'value'       => null,
                'type'        => 'integer',
                'description' => 'ID sesi default jika siswa tidak punya mapping',
            ],
        ];

        foreach ($seeds as $seed) {
            $seed['created_at'] = $now;
            $seed['updated_at'] = $now;
            DB::table('settings')->updateOrInsert(['key' => $seed['key']], $seed);
        }
    }

    public function down(): void
    {
        // Remove session override column from classes
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'school_session_id')) {
                $table->dropForeign(['school_session_id']);
                $table->dropColumn('school_session_id');
            }
        });

        Schema::dropIfExists('grade_session_settings');
        Schema::dropIfExists('school_sessions');

        // Remove seeded settings keys
        DB::table('settings')->whereIn('key', [
            'simulation_enabled',
            'simulation_date',
            'simulation_time',
            'simulation_day_override',
            'multiple_sessions_enabled',
            'default_school_session_id',
        ])->delete();
    }
};
