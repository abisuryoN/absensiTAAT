<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_subject_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha', 'dispensasi']);
            $table->text('note')->nullable();
            $table->timestamps();

            // No soft delete — permanent data
            $table->unique(['attendance_subject_id', 'student_id'], 'att_subject_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_subject_details');
    }
};
