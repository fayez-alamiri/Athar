<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * All quotes (quick and book) published by this user account.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Direct like pivot records created by this user.
     */
    public function quoteLikes(): HasMany
    {
        return $this->hasMany(QuoteLike::class);
    }

    /**
     * Quotes liked by this user.
     */
    public function likedQuotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'quote_likes')
            ->withTimestamps();
    }

    /**
     * Direct saved-quote pivot records created by this user.
     */
    public function savedQuoteRecords(): HasMany
    {
        return $this->hasMany(SavedQuote::class);
    }

    /**
     * Quotes saved (bookmarked) by this user.
     */
    public function savedQuotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'saved_quotes')
            ->withTimestamps();
    }

    /**
     * Resolve the URL or Data URI for the user's uploaded avatar, or null if using default avatar.
     */
    public function avatarUrl(): ?string
    {
        $raw = trim((string) $this->avatar_path);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'data:image/') || preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        return '/storage/' . ltrim($raw, '/');
    }

    /**
     * Compute the real total number of likes received across all posts owned by this user.
     */
    public function receivedLikesCount(): int
    {
        // The application startup migration ensures both tables exist. Avoid
        // two schema-introspection queries each time the navbar renders this count.
        return QuoteLike::query()
            ->whereIn('quote_id', $this->quotes()->select('id'))
            ->count();
    }
}