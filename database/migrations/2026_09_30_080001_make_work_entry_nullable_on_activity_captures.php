<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A screen capture belongs to a work session. Because per-task work entries are
 * not yet recorded, work_entry_id is made optional so the desktop monitoring
 * component can attach captures to the attendance session directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_captures', function (Blueprint $table) {
            $table->dropForeign(['work_entry_id']);
            $table->unsignedBigInteger('work_entry_id')->nullable()->change();
            $table->foreign('work_entry_id')->references('work_entry_id')->on('work_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_captures', function (Blueprint $table) {
            $table->dropForeign(['work_entry_id']);
            $table->unsignedBigInteger('work_entry_id')->nullable(false)->change();
            $table->foreign('work_entry_id')->references('work_entry_id')->on('work_entries');
        });
    }
};
