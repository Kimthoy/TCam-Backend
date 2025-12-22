<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
            Schema::create('industries', function (Blueprint $table) {
                $table->id();

                // Industry info
                $table->string('industry_name');
                $table->text('industry_description')->nullable();

                // Solutions list (JSON)
                $table->json('solutions')->nullable();

                // Management
                $table->boolean('status')->default(true);
                $table->integer('sort_order')->default(0);

                $table->timestamps();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('industries');
    }
};
