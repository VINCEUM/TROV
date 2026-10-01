<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('task_id')->constrained('tasks', 'task_id');
            $table->foreignId('worker_id')->constrained('users', 'user_id');
            $table->foreignId('assigned_by')->constrained('users', 'user_id');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
