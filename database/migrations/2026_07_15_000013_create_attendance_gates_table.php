<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('time_in');
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha']);
            $table->enum('method', ['barcode', 'qr_code', 'manual'])->default('barcode');
            $table->text('note')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One student, one scan per day — NO soft delete (permanent data)
            $table->unique(['student_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['academic_year_id', 'semester_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_gates');
    }
};
