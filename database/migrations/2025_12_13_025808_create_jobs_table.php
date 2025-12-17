<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Main Job Table (ONE)
        |--------------------------------------------------------------------------
        */
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->string('job_slug')->unique();
            $table->string('location')->nullable();
            $table->date('closing_date')->nullable();
            $table->unsignedInteger('hiring_number')->default(1);
            $table->text('job_summary')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Qualifications (ONE)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();
            $table->string('education_level')->nullable();
            $table->string('experience_required')->nullable();
            $table->text('technical_skills')->nullable();
            $table->text('soft_skills')->nullable();
            $table->string('language_requirement')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Application Info (ONE)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_application_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('telegram_link')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Responsibilities (LIST)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();
            $table->text('responsibility_text');
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Benefits (LIST)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();
            $table->string('benefit_title');
            $table->text('benefit_description')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Certifications (LIST)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();
            $table->string('certification_name');
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Personal Attributes (LIST)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_personal_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();
            $table->string('attribute_text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_personal_attributes');
        Schema::dropIfExists('job_certifications');
        Schema::dropIfExists('job_benefits');
        Schema::dropIfExists('job_responsibilities');
        Schema::dropIfExists('job_application_infos');
        Schema::dropIfExists('job_qualifications');
        Schema::dropIfExists('jobs');
    }
};
