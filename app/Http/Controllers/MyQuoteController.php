<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MyQuoteController extends Controller
{
    /**
     * Display all quotes owned by the currently signed-in user with type filters and search.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $type = $request->query('type');
        if (! in_array($type, [Quote::TYPE_QUICK, Quote::TYPE_BOOK], true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        $query = $user->quotes()
            ->with(['user:id,name,avatar_path'])
            ->withCount('likes')
            ->withExists([
                'likes as is_liked_by_auth' => fn ($q) => $q->where('user_id', $user->id),
                'saves as is_saved_by_auth' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->filterByType($type === 'all' ? null : $type)
            ->search($search)
            ->latest('created_at')
            ->latest('id');

        $quotes = $query->paginate(9)->withQueryString();

        // Retrieve all filtered tab totals with one database round trip.
        $countSummary = $user->quotes()
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

        $totalOwnedQuotes = $user->quotes()->count();

        return view('my-quotes.index', [
            'quotes' => $quotes,
            'activeType' => $type,
            'search' => $search,
            'counts' => $counts,
            'totalOwnedQuotes' => $totalOwnedQuotes,
        ]);
    }

    /**
     * Show the edit form for a quote owned by the authenticated user.
     */
    public function edit(Request $request, Quote $quote): View
    {
        Gate::authorize('update', $quote);

        return view('my-quotes.edit', [
            'quote' => $quote,
        ]);
    }

    /**
     * Update a quote owned by the authenticated user.
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        Gate::authorize('update', $quote);

        $validated = $request->validated();

        if ($quote->isBookQuote()) {
            $coverPath = $quote->book_cover_path;

            if ($request->hasFile('book_cover')) {
                $file = $request->file('book_cover');

                if (app()->environment('production')) {
                    $mime = $file->getMimeType() ?: 'image/jpeg';
                    $newCoverPath = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                } else {
                    $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
                    $safeFilename = 'cover_' . Str::uuid()->toString() . '.' . $extension;
                    $newCoverPath = $file->storeAs('book-covers', $safeFilename, 'public');
                }

                if (! empty($coverPath) && str_starts_with($coverPath, 'book-covers/') && Storage::disk('public')->exists($coverPath)) {
                    Storage::disk('public')->delete($coverPath);
                }

                $coverPath = $newCoverPath;
            }

            $quote->update([
                'book_title' => $validated['book_title'],
                'attributed_author' => $validated['attributed_author'],
                'quote_text' => $validated['quote_text'],
                'book_cover_path' => $coverPath,
            ]);
        } else {
            $updateData = [
                'quote_text' => $validated['quote_text'],
            ];

            if ($request->has('attributed_author')) {
                $updateData['attributed_author'] = $validated['attributed_author'] ?? null;
            }

            $quote->update($updateData);
        }

        $quote->syncCatalogRelations();

        return redirect()->route('my-quotes.index')
            ->with('status', __('Your quote has been updated successfully.'));
    }

    /**
     * Delete a single quote owned by the authenticated user.
     */
    public function destroy(Request $request, Quote $quote): RedirectResponse
    {
        Gate::authorize('delete', $quote);

        $coverPath = $quote->book_cover_path;

        DB::transaction(function () use ($quote) {
            $quote->delete();
        });

        if (! empty($coverPath) && str_starts_with($coverPath, 'book-covers/') && Storage::disk('public')->exists($coverPath)) {
            Storage::disk('public')->delete($coverPath);
        }

        return redirect()->back()
            ->with('status', __('Your quote has been deleted.'));
    }

    /**
     * Delete ALL posts owned by the currently authenticated user across both post types.
     * Never trusts a client-supplied user ID.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $user = $request->user();

        $ownedQuotes = $user->quotes()->get(['id', 'book_cover_path']);
        $deletedCount = $ownedQuotes->count();

        if ($deletedCount === 0) {
            return redirect()->route('my-quotes.index')
                ->with('status', __('You have no published quotes to delete.'));
        }

        $coverPaths = $ownedQuotes
            ->pluck('book_cover_path')
            ->filter(fn ($path) => is_string($path) && str_starts_with($path, 'book-covers/'))
            ->values()
            ->all();

        DB::transaction(function () use ($user) {
            $user->quotes()->delete();
        });

        foreach ($coverPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return redirect()->route('my-quotes.index')
            ->with('status', __('All of your published quotes (:count) have been deleted.', ['count' => $deletedCount]));
    }
}