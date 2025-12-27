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
        // Create partner_with_us_sections table
        Schema::create('partner_with_us_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->timestamps();
        });

        // Create partner_with_us_cards table
        Schema::create('partner_with_us_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')
                  ->constrained('partner_with_us_sections')
                  ->onDelete('cascade');
            $table->string('icon', 50);
            $table->string('icon_color', 50)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_with_us_cards');
        Schema::dropIfExists('partner_with_us_sections');
    }
};
