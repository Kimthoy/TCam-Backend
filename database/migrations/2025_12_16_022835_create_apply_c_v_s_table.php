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
        Schema::create('apply_c_v_s', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('jobs')
                ->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->string('position_apply');
            $table->string('email');
            $table->string('phone_number', 50);

            $table->string('hear_about_job')->nullable();
            $table->string('referral_name')->nullable();

            $table->string('cv_file');

            $table->boolean('consent')->default(false);
            $table->string('status')->default("Pending");

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apply_c_v_s');
    }
};
