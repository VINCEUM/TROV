<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->foreignId('client_id')->constrained('clients', 'client_id');
            $table->string('project_name', 150);
            $table->string('status', 20)->default('Active');
            $table->foreignId('created_by')->constrained('users', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
