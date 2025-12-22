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
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string("app_name")->nullable();
            $table->string("app_sort_desc")->nullable();
            $table->string("abstract_desc")->nullable();
            $table->string("app_logo")->nullable();
            $table->string("contact_email")->nullable();
            $table->string("contact_number")->nullable();
            $table->text("contact_address")->nullable();
            $table->text("contact_facebook")->nullable();
            $table->text("contact_youtube")->nullable();
            $table->text("contact_telegram")->nullable();
            $table->string("footer_ownership")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
