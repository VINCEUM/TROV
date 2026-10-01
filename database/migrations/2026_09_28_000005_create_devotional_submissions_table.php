<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devotional_submissions', function (Blueprint $table) {
            $table->id('devotional_id');
            $table->foreignId('worker_id')->constrained('users', 'user_id');
            $table->foreignId('shift_assignment_id')->constrained('shift_assignments', 'shift_assignment_id');
            $table->date('workday');
            $table->string('file_path', 255);
            $table->timestamp('submitted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devotional_submissions');
    }
};
