<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'bio'];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function scopeWithPublishedQuotes(Builder $query): Builder
    {
        return $query->whereHas('quotes');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
