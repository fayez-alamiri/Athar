<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the unified quotes table supporting both quick user quotes and book quotes.
     */
    public function up(): void
    {
        if (Schema::hasTable('quotes')) {
            return;
        }

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->default('quick'); // 'quick' | 'book'
            $table->text('quote_text');

            // Book-quote specific metadata (nullable for quick user quotes)
            $table->string('book_title', 150)->nullable();
            $table->string('book_cover_path')->nullable();
            $table->string('attributed_author', 120)->nullable();

            $table->timestamps();

            // Indexes for feed filtering, user management, and search
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('book_title');
            $table->index('attributed_author');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};