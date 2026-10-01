<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id('shift_assignment_id');
            $table->foreignId('worker_id')->constrained('users', 'user_id');
            $table->string('shift_type', 20); // Morning | Night
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->foreignId('assigned_by')->constrained('users', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
