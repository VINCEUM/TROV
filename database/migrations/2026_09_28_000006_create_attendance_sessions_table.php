<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->foreignId('worker_id')->constrained('users', 'user_id');
            $table->foreignId('shift_assignment_id')->constrained('shift_assignments', 'shift_assignment_id');
            $table->foreignId('devotional_id')->constrained('devotional_submissions', 'devotional_id');
            $table->dateTime('clock_in_at');
            $table->dateTime('clock_out_at')->nullable();
            $table->integer('active_seconds')->default(0);
            $table->integer('idle_seconds')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
