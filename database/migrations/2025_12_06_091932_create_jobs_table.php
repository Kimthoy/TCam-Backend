<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('company', 150)->nullable();
            $table->string('location', 150)->nullable();
            $table->string('salary', 100)->nullable();
            $table->string('job_type', 100)->nullable();
            $table->string('experience', 100)->nullable();

            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('benefits')->nullable();

            $table->string('apply_email', 191)->nullable();
            $table->string('apply_link', 500)->nullable();
            $table->date('deadline')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_closed')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('location');
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
