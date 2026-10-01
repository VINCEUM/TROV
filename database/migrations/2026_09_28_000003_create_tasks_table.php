<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('task_id');
            $table->foreignId('project_id')->constrained('projects', 'project_id');
            $table->string('title', 150);
            $table->text('brief')->nullable();
            $table->string('status', 20)->default('Assigned'); // Assigned | In Progress | Continuation | Done
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
