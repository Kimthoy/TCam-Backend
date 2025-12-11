
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('category_id')
                ->constrained('product_categories')
                ->onDelete('cascade');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('feature_image')->nullable(); // e.g. uploads/products/123.jpg
            $table->boolean('is_published')->default(true);
            $table->softDeletes(); // deleted_at
            $table->timestamps();

            $table->index('is_published');
            $table->index('category_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
