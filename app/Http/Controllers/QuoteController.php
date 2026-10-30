<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookQuoteRequest;
use App\Http\Requests\StoreQuickQuoteRequest;
use App\Models\Quote;
use App\Services\QuoteOfTheDayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuoteController extends Controller
{
    /**
     * Display the public quote feed with type filtering, search, sorting, and pagination.
     */
    public function index(Request $request, QuoteOfTheDayService $quoteOfTheDayService): View
    {
        $type = $request->query('type');
        if (! in_array($type, [Quote::TYPE_QUICK, Quote::TYPE_BOOK], true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'latest');
        if (! in_array($sort, ['latest', 'popular'], true)) {
            $sort = 'latest';
        }

        $query = Quote::query()
            ->with(['user:id,name,avatar_path', 'author:id,name,slug', 'book:id,title,slug,cover_path'])
            ->withCount('likes')
            ->filterByType($type === 'all' ? null : $type)
            ->search($search);

        if (Auth::check()) {
            $userId = Auth::id();
            $query->withExists([
                'likes as is_liked_by_auth' => fn ($q) => $q->where('user_id', $userId),
                'saves as is_saved_by_auth' => fn ($q) => $q->where('user_id', $userId),
            ]);
        }

        if ($sort === 'popular') {
            $query->orderByDesc('likes_count')->latest('id');
        } else {
            $query->latest('created_at')->latest('id');
        }

        $quotes = $query->paginate(9)->withQueryString();

        // Fetch all three tab totals in one database round trip. This matters
        // for Neon-backed deployments where each separate query has network cost.
        $countSummary = Quote::query()
            ->search($search)
            ->selectRaw(
                'COUNT(*) AS all_count, '
                . 'COALESCE(SUM(CASE WHEN type = ? THEN 1 ELSE 0 END), 0) AS quick_count, '
                . 'COALESCE(SUM(CASE WHEN type = ? THEN 1 ELSE 0 END), 0) AS book_count',
                [Quote::TYPE_QUICK, Quote::TYPE_BOOK]
            )
            ->first();

        $counts = [
            'all' => (int) $countSummary->all_count,
            'quick' => (int) $countSummary->quick_count,
            'book' => (int) $countSummary->book_count,
        ];

        return view('quotes.index', [
            'quotes' => $quotes,
            'activeType' => $type,
            'search' => $search,
            'sort' => $sort,
            'counts' => $counts,
            // Keep filtered and searched results self-contained: the daily feature
            // quote is shown only on the unfiltered homepage, not mixed into results.
            'quoteOfTheDay' => ($type === 'all' && $search === '')
                ? $quoteOfTheDayService->forToday()
                : null,
        ]);
    }

    /**
     * Show the form for publishing a quick user quote.
     */
    public function createQuick(): View
    {
        return view('quotes.create-quick');
    }

    /**
     * Validate and store a newly published quick user quote.
     */
    public function storeQuick(StoreQuickQuoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $quote = Quote::create([
            'user_id' => $request->user()->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => $validated['quote_text'],
            'book_title' => null,
            'book_cover_path' => null,
            'attributed_author' => $validated['attributed_author'] ?? null,
        ]);

        $quote->syncCatalogRelations();

        return redirect()->route('home')
            ->with('status', __('Your quick quote has been published successfully.'));
    }

    /**
     * Show the form for publishing a quote from a book.
     */
    public function createBook(): View
    {
        return view('quotes.create-book');
    }

    /**
     * Validate and store a newly published book quote with its uploaded cover image.
     */
    public function storeBook(StoreBookQuoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('book_cover');

        if (app()->environment('production')) {
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $storedPath = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        } else {
            $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
            $safeFilename = 'cover_' . Str::uuid()->toString() . '.' . $extension;
            $storedPath = $file->storeAs('book-covers', $safeFilename, 'public');
        }

        $quote = Quote::create([
            'user_id' => $request->user()->id,
            'type' => Quote::TYPE_BOOK,
            'quote_text' => $validated['quote_text'],
            'book_title' => $validated['book_title'],
            'book_cover_path' => $storedPath,
            'attributed_author' => $validated['attributed_author'],
        ]);

        $quote->syncCatalogRelations();

        return redirect()->route('home')
            ->with('status', __('Your book quote has been published successfully.'));
    }

    /** Display the deterministic featured quotation for the current Riyadh day. */
    public function daily(QuoteOfTheDayService $quoteOfTheDayService): View
    {
        return view('quotes.daily', [
            'quote' => $quoteOfTheDayService->forToday(),
            'today' => now('Asia/Riyadh'),
        ]);
    }

    /** Immersive, one-quotation-at-a-time reading mode. */
    public function zen(Request $request): View
    {
        $quotes = Quote::query()
            ->with(['user:id,name,avatar_path', 'author:id,name,slug', 'book:id,title,slug,cover_path'])
            ->withCount('likes')
            ->orderBy('id')
            ->paginate(1)
            ->withQueryString();

        if ($request->user()) {
            $userId = $request->user()->id;
            $quotes->getCollection()->each(function (Quote $quote) use ($userId): void {
                $quote->setAttribute('is_liked_by_auth', $quote->likes()->where('user_id', $userId)->exists());
                $quote->setAttribute('is_saved_by_auth', $quote->saves()->where('user_id', $userId)->exists());
            });
        }

        return view('quotes.zen', compact('quotes'));
    }

}