<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Countries
        |--------------------------------------------------------------------------
        */
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_name');              // Cambodia, Lao PDR, Myanmar
            $table->string('icon_color')->nullable();    // blue, red, yellow
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Offices
        |--------------------------------------------------------------------------
        */
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('office_name');               // Phnom Penh Office
            $table->text('address');                     // Full address
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Office Phones
        |--------------------------------------------------------------------------
        */
        Schema::create('office_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('phone_number');              // (+855) 23 961 222
            $table->string('label')->nullable();         // reception, call center
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Office Emails
        |--------------------------------------------------------------------------
        */
        Schema::create('office_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('email_address');             // support@firstcambodia.com.kh
            $table->string('label')->nullable();         // support, info
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Office Websites
        |--------------------------------------------------------------------------
        */
        Schema::create('office_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('website_url');               // https://firstcambodia.com.kh
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_websites');
        Schema::dropIfExists('office_emails');
        Schema::dropIfExists('office_phones');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('countries');
    }
};
