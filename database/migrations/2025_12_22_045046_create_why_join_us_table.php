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
        Schema::create('why_join_us', function (Blueprint $table) {
              $table->id();
            $table->string('section_tag')->nullable(); 
            $table->string('section_title'); 
            $table->text('section_description')->nullable();
            $table->json('items')->nullable(); 
            $table->boolean('status')->default(true); 
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('why_join_us');
    }
};
