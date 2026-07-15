<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('barcode_id', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('barcode_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
