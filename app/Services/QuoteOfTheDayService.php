<?php

namespace App\Services;

use App\Models\Quote;
use Illuminate\Support\Facades\DB;

/**
 * Selects one stable quote per Riyadh calendar day.
 * The selected quote id is persisted in daily_quotes so multiple instances and
 * restarts continue to show the same quote for that date.
 */
class QuoteOfTheDayService
{
    public function forToday(): ?Quote
    {
        $today = now('Asia/Riyadh')->toDateString();
        $selectedId = DB::table('daily_quotes')->where('quote_date', $today)->value('quote_id');

        if (! $selectedId) {
            $quoteCount = Quote::query()->count();
            if ($quoteCount === 0) {
                return null;
            }

            $offset = hexdec(substr(hash('sha256', 'athar:' . $today), 0, 8)) % $quoteCount;
            $candidateId = Quote::query()->orderBy('id')->skip($offset)->value('id');
            if (! $candidateId) {
                return null;
            }

            DB::table('daily_quotes')->insertOrIgnore([
                'quote_date' => $today,
                'quote_id' => $candidateId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $selectedId = DB::table('daily_quotes')->where('quote_date', $today)->value('quote_id');
        }

        return Quote::query()
            ->with(['user:id,name,avatar_path', 'author:id,name,slug', 'book:id,title,slug,cover_path'])
            ->withCount('likes')
            ->find($selectedId);
    }
}
