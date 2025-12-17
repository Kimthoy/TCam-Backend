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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();

            //basic info
            $table->string('job_title');
            $table->string('company', 225)->nullable();
            $table->string('location')->nullable();
            $table->string('experience')->nullable(); 
            $table->string('skills')->nullable();
            $table->string('salary', 100)->nullable(); 
            $table->text('benefits')->nullable();    
            $table->text('description')->nullable();
            $table->string('job_type', 100)->nullable();
            $table->string('feature_image')->nullable(); 

            //HR fields
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            //Recruitment Fields
            $table->date('deadline')->nullable();
            $table->boolean('featured')->default(false);

            // Application Requirement
            $table->string('education_level')->nullable();
            $table->string('language_requirements')->nullable();


            //listing pages
            $table->string('slug')->unique();
            $table->boolean('status')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
