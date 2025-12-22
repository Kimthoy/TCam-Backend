<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Core event info
            $table->string('title');                    // Event title
            $table->string('subtitle')->nullable();     // Short highlight
            $table->date('event_date')->nullable();     // Event date
            $table->string('location')->nullable();     // Event location
            $table->string('category')->default('events');

            // Main poster image (NO banner)
            $table->string('poster_image')->nullable();

            // Rich content
            $table->longText('description')->nullable();

            /**
             * Dynamic JSON sections
             */

            // Participants:
            // [{ "name": "", "role": "", "photo": "" }]
            $table->json('participants')->nullable();

            // Certifications:
            // ["Oracle Cloud Database Services 2025 Certified Professional"]
            $table->json('certifications')->nullable();

            // Certificates / Media:
            // [{ "title": "", "image": "" }]
            $table->json('certificates')->nullable();

            // Status
            $table->boolean('is_published')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
