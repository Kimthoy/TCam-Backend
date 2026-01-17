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
       Schema::create('sub_products', function (Blueprint $table) {
    $table->id();

    $table->foreignId('product_id')
          ->constrained('products')
          ->onDelete('cascade');

    $table->string('name'); // Recruitment, Training
    $table->text('description')->nullable();
    $table->decimal('price', 12, 2)->nullable();
    $table->boolean('is_active')->default(true);

    $table->timestamps();
});

Schema::create('sub_product_images', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sub_product_id')
          ->constrained('sub_products')
          ->onDelete('cascade');

    $table->string('image_path');
    $table->boolean('is_primary')->default(false);

    $table->timestamps();
});

Schema::create('sub_product_properties', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sub_product_id')
          ->constrained('sub_products')
          ->onDelete('cascade');

    $table->string('key');
    $table->string('value');

    $table->timestamps();

    $table->unique(['sub_product_id', 'key']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_products');
        Schema::dropIfExists('sub_product_images');
        Schema::dropIfExists('sub_product_properties');
    }
};
