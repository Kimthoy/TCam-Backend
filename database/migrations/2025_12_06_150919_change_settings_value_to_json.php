<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            /**
             * Convert `value` from TEXT → JSON
             *
             * IMPORTANT:
             * - Your DB must support JSON column type: MySQL 5.7+ or MariaDB 10.2.7+
             */
            $table->json('value')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Revert back to TEXT if needed
            $table->text('value')->nullable()->change();
        });
    }
};
