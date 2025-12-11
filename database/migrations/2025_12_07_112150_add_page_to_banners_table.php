<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_xx_xx_add_page_to_banners_table.php
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('page')->default('home')->index();
        });
    }

    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('page');
        });
    }
};
