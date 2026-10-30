<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'cover_path'];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function coverUrl(): ?string
    {
        $path = trim((string) $this->cover_path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'data:image/') || preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
