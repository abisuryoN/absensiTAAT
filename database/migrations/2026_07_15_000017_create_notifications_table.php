<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('channel', 20)->default('whatsapp');
            $table->string('phone_number', 20);
            $table->text('message');
            $table->enum('status', ['pending', 'queued', 'sent', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['student_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
