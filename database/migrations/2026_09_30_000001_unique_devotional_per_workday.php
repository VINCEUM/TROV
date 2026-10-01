<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforces one devotional submission per editor per workday at the database
 * level (functional requirement 2). Any existing duplicates are collapsed to
 * the earliest submission before the unique index is added.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Collapse any existing duplicates first. The multi-table DELETE is
        // MySQL syntax, and only real (MySQL) data can contain duplicates, so
        // it is skipped on other drivers such as the sqlite test database.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'DELETE d1 FROM devotional_submissions d1 '
                . 'JOIN devotional_submissions d2 '
                . 'ON d1.worker_id = d2.worker_id AND d1.workday = d2.workday '
                . 'AND d1.devotional_id > d2.devotional_id'
            );
        }

        Schema::table('devotional_submissions', function (Blueprint $table) {
            $table->unique(['worker_id', 'workday'], 'devotional_one_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('devotional_submissions', function (Blueprint $table) {
            $table->dropUnique('devotional_one_per_day');
        });
    }
};
