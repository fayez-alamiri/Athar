<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\SavedQuote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SavedQuoteController extends Controller
{
    /**
     * Display the private Saved Quotes (المحفوظات) page for the authenticated user.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $type = $request->query('type');
        if (! in_array($type, [Quote::TYPE_QUICK, Quote::TYPE_BOOK], true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        $query = $user->savedQuotes()
            ->with(['user:id,name,avatar_path'])
            ->withCount('likes')
            ->withExists([
                'likes as is_liked_by_auth' => fn ($q) => $q->where('user_id', $user->id),
                'saves as is_saved_by_auth' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->filterByType($type === 'all' ? null : $type)
            ->search($search)
            ->orderByPivot('created_at', 'desc');

        $quotes = $query->paginate(9)->withQueryString();

        // Retrieve all filter-tab totals with one query instead of three sequential
        // round trips to the production database.
        $countSummary = $user->savedQuotes()
            ->search($search)
            ->selectRaw(
                'COUNT(*) AS all_count, '
                . 'COALESCE(SUM(CASE WHEN quotes.type = ? THEN 1 ELSE 0 END), 0) AS quick_count, '
                . 'COALESCE(SUM(CASE WHEN quotes.type = ? THEN 1 ELSE 0 END), 0) AS book_count',
                [Quote::TYPE_QUICK, Quote::TYPE_BOOK]
            )
            ->first();

        $counts = [
            'all' => (int) $countSummary->all_count,
            'quick' => (int) $countSummary->quick_count,
            'book' => (int) $countSummary->book_count,
        ];

        return view('quotes.saved', [
            'quotes' => $quotes,
            'activeType' => $type,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Toggle saving/unsaving a quote for the authenticated user.
     * Saving creates only a SavedQuote relationship and never duplicates the Quote record or changes ownership.
     */
    public function toggle(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $saved = DB::transaction(function () use ($user, $quote): bool {
            $existing = SavedQuote::query()
                ->where('user_id', $user->id)
                ->where('quote_id', $quote->id)
                ->first();

            if ($existing) {
                $existing->delete();
                return false;
            }

            SavedQuote::firstOrCreate([
                'user_id' => $user->id,
                'quote_id' => $quote->id,
            ]);

            return true;
        });

        $message = $saved
            ? __('Quote saved to your Saved Quotes.')
            : __('Quote removed from your Saved Quotes.');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'quote_id' => $quote->id,
                'saved' => $saved,
                'label' => $saved ? __('Saved') : __('Save'),
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('status', $message);
    }
}