<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('slug', 180)->unique();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 150)->unique();
            $table->string('slug', 190)->unique();
            $table->text('cover_path')->nullable();
            $table->timestamps();
        });

        Schema::table('quotes', function (Blueprint $table): void {
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
        });

        // Backfill catalog records from existing quote fields without changing
        // the original text/path columns or deleting any existing records.
        $quotes = DB::table('quotes')->orderBy('id')->get([
            'id', 'type', 'attributed_author', 'book_title', 'book_cover_path',
        ]);

        foreach ($quotes as $quote) {
            $authorId = null;
            $bookId = null;

            $authorName = trim((string) $quote->attributed_author);
            if ($authorName !== '') {
                $author = DB::table('authors')->where('name', $authorName)->first();
                if (! $author) {
                    $slugBase = Str::slug($authorName) ?: 'author-' . substr(sha1($authorName), 0, 10);
                    $slug = $slugBase;
                    $suffix = 2;
                    while (DB::table('authors')->where('slug', $slug)->exists()) {
                        $slug = $slugBase . '-' . $suffix++;
                    }
                    $authorId = DB::table('authors')->insertGetId([
                        'name' => $authorName,
                        'slug' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $authorId = $author->id;
                }
            }

            $bookTitle = trim((string) $quote->book_title);
            if ($quote->type === 'book' && $bookTitle !== '') {
                $book = DB::table('books')->where('title', $bookTitle)->first();
                if (! $book) {
                    $slugBase = Str::slug($bookTitle) ?: 'book-' . substr(sha1($bookTitle), 0, 10);
                    $slug = $slugBase;
                    $suffix = 2;
                    while (DB::table('books')->where('slug', $slug)->exists()) {
                        $slug = $slugBase . '-' . $suffix++;
                    }
                    $bookId = DB::table('books')->insertGetId([
                        'title' => $bookTitle,
                        'slug' => $slug,
                        'cover_path' => $quote->book_cover_path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $bookId = $book->id;
                    if (empty($book->cover_path) && ! empty($quote->book_cover_path)) {
                        DB::table('books')->where('id', $bookId)->update([
                            'cover_path' => $quote->book_cover_path,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::table('quotes')->where('id', $quote->id)->update([
                'author_id' => $authorId,
                'book_id' => $bookId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('author_id');
            $table->dropConstrainedForeignId('book_id');
        });

        Schema::dropIfExists('books');
        Schema::dropIfExists('authors');
    }
};
