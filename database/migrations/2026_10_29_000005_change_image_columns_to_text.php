<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->text('book_cover_path')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('avatar_path')->nullable()->change();
        });

        // Add non-destructive composite indexes to accelerate feed filtering, sorting, and user lookups
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS quotes_type_created_at_idx ON quotes (type, created_at DESC)');
            DB::statement('CREATE INDEX IF NOT EXISTS quotes_user_id_created_at_idx ON quotes (user_id, created_at DESC)');
            DB::statement('CREATE INDEX IF NOT EXISTS quote_likes_quote_id_user_id_idx ON quote_likes (quote_id, user_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS saved_quotes_user_id_created_at_idx ON saved_quotes (user_id, created_at DESC)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS quotes_type_created_at_idx');
            DB::statement('DROP INDEX IF EXISTS quotes_user_id_created_at_idx');
            DB::statement('DROP INDEX IF EXISTS quote_likes_quote_id_user_id_idx');
            DB::statement('DROP INDEX IF EXISTS saved_quotes_user_id_created_at_idx');
        }
    }
};