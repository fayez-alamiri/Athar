<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    /** @use HasFactory<\Database\Factories\QuoteFactory> */
    use HasFactory;

    public const TYPE_QUICK = 'quick';
    public const TYPE_BOOK = 'book';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'quote_text',
        'book_title',
        'book_cover_path',
        'attributed_author',
        'author_id',
        'book_id',
    ];

    /**
     * The platform user account that published this quote.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Original person credited for the quotation, distinct from the publisher. */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /** Book catalog record for book-type quotations. */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Synchronize normalized author/book catalog records while preserving the
     * legacy text fields used by older content and existing templates.
     */
    public function syncCatalogRelations(): void
    {
        $authorId = null;
        $authorName = trim((string) $this->attributed_author);
        if ($authorName !== '') {
            $slug = Str::slug($authorName) ?: 'author-' . substr(sha1($authorName), 0, 10);
            if (Author::query()->where('slug', $slug)->where('name', '!=', $authorName)->exists()) {
                $slug .= '-' . substr(sha1($authorName), 0, 6);
            }
            $author = Author::query()->firstOrCreate(['name' => $authorName], ['slug' => $slug]);
            $authorId = $author->id;
        }

        $bookId = null;
        $bookTitle = trim((string) $this->book_title);
        if ($this->isBookQuote() && $bookTitle !== '') {
            $slug = Str::slug($bookTitle) ?: 'book-' . substr(sha1($bookTitle), 0, 10);
            if (Book::query()->where('slug', $slug)->where('title', '!=', $bookTitle)->exists()) {
                $slug .= '-' . substr(sha1($bookTitle), 0, 6);
            }
            $book = Book::query()->firstOrCreate(
                ['title' => $bookTitle],
                ['slug' => $slug, 'cover_path' => $this->book_cover_path]
            );
            if (! empty($this->book_cover_path) && $book->cover_path !== $this->book_cover_path) {
                $book->update(['cover_path' => $this->book_cover_path]);
            }
            $bookId = $book->id;
        }

        if ($this->author_id !== $authorId || $this->book_id !== $bookId) {
            $this->forceFill(['author_id' => $authorId, 'book_id' => $bookId])->saveQuietly();
        }
    }

    /**
     * Like records belonging to this quote.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(QuoteLike::class);
    }

    /**
     * Users who liked this quote.
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'quote_likes')
            ->withTimestamps();
    }

    /**
     * SavedQuote records belonging to this quote.
     */
    public function saves(): HasMany
    {
        return $this->hasMany(SavedQuote::class);
    }

    /**
     * Users who saved (bookmarked) this quote.
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_quotes')
            ->withTimestamps();
    }

    /**
     * Determine if this quote is a book quote.
     */
    public function isBookQuote(): bool
    {
        return $this->type === self::TYPE_BOOK;
    }

    /**
     * Determine if this quote is a quick user quote.
     */
    public function isQuickQuote(): bool
    {
        return $this->type === self::TYPE_QUICK;
    }

    /**
     * Resolve the public URL or persistent Data URI for the book cover image if present.
     */
    public function bookCoverUrl(): ?string
    {
        $rawPath = trim((string) $this->book_cover_path);
        if ($rawPath === '') {
            return null;
        }

        if (str_starts_with($rawPath, 'data:image/') || preg_match('#^https?://#i', $rawPath)) {
            return $rawPath;
        }

        $normalized = str_replace('\\', '/', $rawPath);
        $normalized = ltrim($normalized, '/');
        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, strlen('public/'));
        }

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, 'images/') || file_exists(public_path($normalized))) {
            return '/' . $normalized;
        }

        if (str_starts_with($normalized, 'storage/')) {
            return '/' . $normalized;
        }

        return '/storage/' . $normalized;
    }

    /**
     * Check whether the given user has liked this quote.
     */
    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Check whether the given user has saved this quote.
     */
    public function isSavedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('saves')) {
            return $this->saves->contains('user_id', $user->id);
        }

        return $this->saves()->where('user_id', $user->id)->exists();
    }

    /**
     * Scope to quick user quotes only.
     */
    public function scopeQuick(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_QUICK);
    }

    /**
     * Scope to book quotes only.
     */
    public function scopeBook(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_BOOK);
    }

    /**
     * Scope to filter by quote type ('quick' or 'book') when specified.
     */
    public function scopeFilterByType(Builder $query, ?string $type): Builder
    {
        if (in_array($type, [self::TYPE_QUICK, self::TYPE_BOOK], true)) {
            return $query->where('type', $type);
        }

        return $query;
    }

    /**
     * Scope to search across quote text, book title, attributed author, and publisher name.
     * Automatically uses ILIKE on PostgreSQL and LIKE on SQLite.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $clean = trim((string) $term);

        if ($clean === '') {
            return $query;
        }

        $like = '%' . $clean . '%';
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function (Builder $subQuery) use ($like, $operator) {
            $subQuery->where('quote_text', $operator, $like)
                ->orWhere('book_title', $operator, $like)
                ->orWhere('attributed_author', $operator, $like)
                ->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->where('name', $operator, $like))
                ->orWhereHas('book', fn (Builder $bookQuery) => $bookQuery->where('title', $operator, $like))
                ->orWhereHas('user', function (Builder $userQuery) use ($like, $operator) {
                    $userQuery->where('name', $operator, $like);
                });
        });
    }
}