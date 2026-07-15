<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('phone_secondary', 20)->nullable();
            $table->enum('relationship', ['ayah', 'ibu', 'wali'])->default('ayah');
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
