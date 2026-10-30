<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add avatar_path and locale preference to users table without losing existing data.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 5)->default('ar')->after('avatar_path');
            }
        });
    }

    /**
     * Reverse the migrations safely.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('users', 'avatar_path')) {
                $columnsToDrop[] = 'avatar_path';
            }

            if (Schema::hasColumn('users', 'locale')) {
                $columnsToDrop[] = 'locale';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};