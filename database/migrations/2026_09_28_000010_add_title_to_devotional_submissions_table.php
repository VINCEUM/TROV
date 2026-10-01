<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The prototype's devotional screen captures a "Title or passage" alongside the
 * photo. The Chapter 3 data dictionary lists only the photo, so this adds the
 * field as an optional extension without changing the documented columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devotional_submissions', function (Blueprint $table) {
            $table->string('title', 150)->nullable()->after('workday');
        });
    }

    public function down(): void
    {
        Schema::table('devotional_submissions', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
