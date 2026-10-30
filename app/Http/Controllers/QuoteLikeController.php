<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteLikeController extends Controller
{
    /**
     * Toggle the authenticated user's like on a quote.
     * Enforces uniqueness for (user_id, quote_id) in the database.
     */
    public function toggle(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $liked = DB::transaction(function () use ($user, $quote): bool {
            $existing = QuoteLike::query()
                ->where('user_id', $user->id)
                ->where('quote_id', $quote->id)
                ->first();

            if ($existing) {
                $existing->delete();
                return false;
            }

            QuoteLike::firstOrCreate([
                'user_id' => $user->id,
                'quote_id' => $quote->id,
            ]);

            return true;
        });

        $likesCount = $quote->likes()->count();
        $receivedLikesCount = $user->receivedLikesCount();
        $message = $liked
            ? __('Quote liked.')
            : __('Like removed.');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'quote_id' => $quote->id,
                'liked' => $liked,
                'likes_count' => $likesCount,
                'received_likes_count' => $receivedLikesCount,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('status', $message);
    }
}