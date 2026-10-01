<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // USERS - holds both Owners and Video Editors, distinguished by role.
        // Column names follow the Chapter 3 data dictionary; remember_token and
        // the sessions / password_reset_tokens tables below are framework infrastructure.
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('role', 20)->default('Video Editor'); // Owner | Video Editor
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
