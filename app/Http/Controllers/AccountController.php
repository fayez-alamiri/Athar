<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the authenticated user's My Account page with real database statistics.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Aggregate quote totals in one query instead of three separate queries.
        $quoteStats = $user->quotes()
            ->selectRaw(
                'COUNT(*) AS total_count, '
                . 'COALESCE(SUM(CASE WHEN type = ? THEN 1 ELSE 0 END), 0) AS quick_count, '
                . 'COALESCE(SUM(CASE WHEN type = ? THEN 1 ELSE 0 END), 0) AS book_count',
                [\App\Models\Quote::TYPE_QUICK, \App\Models\Quote::TYPE_BOOK]
            )
            ->first();

        $totalQuotesCount = (int) $quoteStats->total_count;
        $quickQuotesCount = (int) $quoteStats->quick_count;
        $bookQuotesCount = (int) $quoteStats->book_count;
        $receivedLikesCount = $user->receivedLikesCount();
        $savedQuotesCount = $user->savedQuotes()->count();

        return view('account.show', [
            'user' => $user,
            'stats' => [
                'total_quotes' => $totalQuotesCount,
                'quick_quotes' => $quickQuotesCount,
                'book_quotes' => $bookQuotesCount,
                'received_likes' => $receivedLikesCount,
                'saved_quotes' => $savedQuotesCount,
            ],
        ]);
    }

    /**
     * Upload and replace the authenticated user's profile avatar image.
     */
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');

        if (app()->environment('production')) {
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $newPath = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        } else {
            $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
            $safeFilename = 'avatar_' . $user->id . '_' . Str::uuid()->toString() . '.' . $extension;
            $newPath = $file->storeAs('avatars', $safeFilename, 'public');
        }

        $oldPath = $user->avatar_path;

        $user->forceFill([
            'avatar_path' => $newPath,
        ])->save();

        if (! empty($oldPath) && str_starts_with($oldPath, 'avatars/') && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('account.show')
            ->with('status', __('Your profile image has been updated.'));
    }

    /**
     * Remove the authenticated user's profile avatar and revert to the default avatar.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldPath = $user->avatar_path;

        if (! empty($oldPath)) {
            if (str_starts_with($oldPath, 'avatars/') && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $user->forceFill([
                'avatar_path' => null,
            ])->save();
        }

        return redirect()->route('account.show')
            ->with('status', __('Your profile image has been removed.'));
    }
}