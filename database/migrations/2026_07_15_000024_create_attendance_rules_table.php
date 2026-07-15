<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('key', 100);
            $table->text('value');
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};
