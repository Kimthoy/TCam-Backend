<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('About Us');
            $table->string('company_image')->nullable(); // URL or path to image
            $table->year('founding_year')->nullable();
            $table->string('founders_info')->nullable();
            $table->text('intro_text')->nullable();
            $table->json('operational_offices')->nullable(); // store as JSON array
            $table->text('services_description')->nullable();
            $table->text('company_profile')->nullable();
            $table->integer('project_count')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('value_proposition')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
