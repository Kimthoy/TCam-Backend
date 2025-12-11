<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // profile photo path
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('password');
            }

            // quick role (optional) — consider using spatie/permission for real roles
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('photo');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }

            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'role', 'is_active']);
            $table->dropSoftDeletes();
        });
    }
};
