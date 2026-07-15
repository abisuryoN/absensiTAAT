<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->enum('method', ['realtime', 'recap'])->default('realtime');
            $table->text('note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // No soft delete — permanent data
            $table->unique(['schedule_id', 'date']);
            $table->index(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_subjects');
    }
};
