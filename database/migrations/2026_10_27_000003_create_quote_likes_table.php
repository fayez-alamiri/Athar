<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the quote_likes table enforcing one like per (user_id, quote_id) pair.
     */
    public function up(): void
    {
        if (Schema::hasTable('quote_likes')) {
            return;
        }

        Schema::create('quote_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'quote_id']);
            $table->index('quote_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_likes');
    }
};