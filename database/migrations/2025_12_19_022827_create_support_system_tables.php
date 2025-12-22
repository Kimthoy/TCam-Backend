<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Support Section (Main Header)
        |--------------------------------------------------------------------------
        */
        Schema::create('support_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_title');
            $table->text('section_description')->nullable();
            $table->string('iso_certification')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Support Plans (Bronze, Silver, Gold)
        |--------------------------------------------------------------------------
        */
        Schema::create('support_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name'); // Bronze, Silver, Gold
            $table->string('badge_color')->nullable(); // bronze, silver, gold or hex
            $table->string('support_hours_label'); // 8/5, 8/7, 24/7
            $table->string('support_coverage');
            $table->boolean('include_holidays')->default(false);
            $table->boolean('exclude_holidays')->default(false);
            $table->integer('preventive_maintenance_per_year')->default(0);
            $table->string('case_support')->default('Unlimited case support');
            $table->string('cta_text')->default('Contact Now');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Support Plan Features
        |--------------------------------------------------------------------------
        */
        Schema::create('support_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_plan_id')
                  ->constrained('support_plans')
                  ->cascadeOnDelete();
            $table->string('feature_text');
            $table->boolean('is_highlighted')->default(false);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Support Options Section (Bottom Info)
        |--------------------------------------------------------------------------
        */
        Schema::create('support_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_title');
            $table->text('option_description');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_plan_features');
        Schema::dropIfExists('support_plans');
        Schema::dropIfExists('support_options');
        Schema::dropIfExists('support_sections');
    }
};
