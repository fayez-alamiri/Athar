<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\View\View;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(): View
    {
        $authors = Author::query()
            ->withCount('quotes')
            ->withPublishedQuotes()
            ->orderBy('name')
            ->paginate(24);

        return view('authors.index', compact('authors'));
    }

    public function show(Request $request, Author $author): View
    {
        $query = $author->quotes()
            ->with(['user:id,name,avatar_path', 'author:id,name,slug', 'book:id,title,slug,cover_path'])
            ->withCount('likes');

        if ($request->user()) {
            $userId = $request->user()->id;
            $query->withExists([
                'likes as is_liked_by_auth' => fn ($builder) => $builder->where('user_id', $userId),
                'saves as is_saved_by_auth' => fn ($builder) => $builder->where('user_id', $userId),
            ]);
        }

        $quotes = $query->latest('created_at')->paginate(12)->withQueryString();

        return view('authors.show', compact('author', 'quotes'));
    }
}
