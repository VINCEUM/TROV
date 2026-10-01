<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_captures', function (Blueprint $table) {
            $table->id('capture_id');
            $table->foreignId('work_entry_id')->constrained('work_entries', 'work_entry_id');
            $table->foreignId('attendance_id')->constrained('attendance_sessions', 'attendance_id');
            $table->string('file_path', 255);
            $table->timestamp('captured_at')->useCurrent();
            $table->integer('keystroke_count')->default(0);
            $table->integer('mouse_event_count')->default(0);
            $table->tinyInteger('activity_level')->default(0); // 0-100 percent
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_captures');
    }
};
