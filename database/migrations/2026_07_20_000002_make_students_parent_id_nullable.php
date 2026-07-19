<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['parent_id']);

            // Make nullable and re-add with nullOnDelete
            $table->foreignId('parent_id')->nullable()->change();
            $table->foreign('parent_id')->references('id')->on('parents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->foreignId('parent_id')->nullable(false)->change();
            $table->foreign('parent_id')->references('id')->on('parents')->cascadeOnDelete();
        });
    }
};