<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_entries', function (Blueprint $table) {
            $table->id('work_entry_id');
            $table->foreignId('assignment_id')->constrained('task_assignments', 'assignment_id');
            $table->foreignId('worker_id')->constrained('users', 'user_id');
            $table->foreignId('attendance_id')->constrained('attendance_sessions', 'attendance_id');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->text('description')->nullable();
            $table->string('eod_status', 20)->nullable(); // In Progress | Continuation | Done
            $table->text('handover_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_entries');
    }
};
