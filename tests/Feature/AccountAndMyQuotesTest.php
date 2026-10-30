<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteLike;
use App\Models\SavedQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountAndMyQuotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_page_displays_accurate_published_post_and_received_like_statistics(): void
    {
        $user = User::factory()->create(['name' => 'أديب المنصة']);
        $otherReaderA = User::factory()->create();
        $otherReaderB = User::factory()->create();

        $quickQuote = Quote::factory()->create([
            'user_id' => $user->id,
            'type' => Quote::TYPE_QUICK,
        ]);

        $bookQuote = Quote::factory()->book()->create([
            'user_id' => $user->id,
        ]);

        // 2 likes on quickQuote, 1 like on bookQuote = 3 received likes total
        QuoteLike::create(['user_id' => $otherReaderA->id, 'quote_id' => $quickQuote->id]);
        QuoteLike::create(['user_id' => $otherReaderB->id, 'quote_id' => $quickQuote->id]);
        QuoteLike::create(['user_id' => $otherReaderA->id, 'quote_id' => $bookQuote->id]);

        $response = $this->actingAs($user)->get(route('account.show'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['total_quotes'] === 2
                && $stats['quick_quotes'] === 1
                && $stats['book_quotes'] === 1
                && $stats['received_likes'] === 3;
        });
    }

    public function test_user_can_upload_valid_avatar_reject_invalid_files_and_remove_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // 1. Valid avatar upload
        $avatarFile = UploadedFile::fake()->image('profile.png', 300, 300);
        $uploadResponse = $this->actingAs($user)->post(route('account.avatar.update'), [
            'avatar' => $avatarFile,
        ]);

        $uploadResponse->assertRedirect(route('account.show'));
        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        // 2. Invalid file (PDF instead of image) is rejected
        $badFile = UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf');
        $invalidResponse = $this->actingAs($user)
            ->from(route('account.show'))
            ->post(route('account.avatar.update'), [
                'avatar' => $badFile,
            ]);
        $invalidResponse->assertRedirect(route('account.show'));
        $invalidResponse->assertSessionHasErrors(['avatar']);

        // 3. Remove avatar cleans up storage and resets avatar_path to null
        $storedPath = $user->avatar_path;
        $removeResponse = $this->actingAs($user)->delete(route('account.avatar.destroy'));
        $removeResponse->assertRedirect(route('account.show'));

        $user->refresh();
        $this->assertNull($user->avatar_path);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_my_quotes_displays_only_current_users_posts_of_both_types(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $myQuick = Quote::factory()->create([
            'user_id' => $userA->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'اقتباس سريع خاص بالمستخدم الأول في صفحة اقتباساتي.',
        ]);

        $myBook = Quote::factory()->book()->create([
            'user_id' => $userA->id,
            'quote_text' => 'My own published book quote passage.',
            'book_title' => 'My Literary Book',
            'attributed_author' => 'Ibn Khaldun',
        ]);

        $otherUserQuote = Quote::factory()->create([
            'user_id' => $userB->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'اقتباس مستخدم آخر لا يجب أن يظهر في اقتباساتي.',
        ]);

        $response = $this->actingAs($userA)->get(route('my-quotes.index'));

        $response->assertOk();
        $response->assertSee($myQuick->quote_text);
        $response->assertSee($myBook->quote_text);
        $response->assertDontSee($otherUserQuote->quote_text);
        $response->assertSee('حذف جميع منشوراتي');
    }

    public function test_user_can_edit_own_quick_and_book_quotes_and_cannot_edit_others_posts(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $quickQuote = Quote::factory()->create([
            'user_id' => $owner->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'النص الأصلي للاقتباس السريع قبل التعديل.',
        ]);

        $bookQuote = Quote::factory()->book()->create([
            'user_id' => $owner->id,
            'quote_text' => 'Original book passage before editing.',
            'book_title' => 'Old Book Title',
            'attributed_author' => 'Old Author',
        ]);

        // Owner updates quick quote
        $this->actingAs($owner)
            ->put(route('my-quotes.update', $quickQuote), [
                'quote_text' => 'النص المعدل للاقتباس السريع بعد التحديث.',
            ])
            ->assertRedirect(route('my-quotes.index'));

        $this->assertSame('النص المعدل للاقتباس السريع بعد التحديث.', $quickQuote->fresh()->quote_text);

        // Owner updates book quote
        $this->actingAs($owner)
            ->put(route('my-quotes.update', $bookQuote), [
                'book_title' => 'Updated Book Title',
                'attributed_author' => 'Updated Author',
                'quote_text' => 'Updated book passage after editing.',
            ])
            ->assertRedirect(route('my-quotes.index'));

        $this->assertSame('Updated Book Title', $bookQuote->fresh()->book_title);
        $this->assertSame('Updated Author', $bookQuote->fresh()->attributed_author);

        // Intruder cannot access edit page or update owner's quote
        $this->actingAs($intruder)
            ->get(route('my-quotes.edit', $quickQuote))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->put(route('my-quotes.update', $quickQuote), [
                'quote_text' => 'محاولة تعديل غير مصرح بها.',
            ])
            ->assertForbidden();
    }

    public function test_individual_and_bulk_delete_are_authorized_and_remove_only_current_users_posts(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $quoteA1 = Quote::factory()->create(['user_id' => $userA->id]);
        $quoteA2 = Quote::factory()->book()->create(['user_id' => $userA->id]);
        $quoteA3 = Quote::factory()->create(['user_id' => $userA->id]);

        $quoteB1 = Quote::factory()->create(['user_id' => $userB->id]);

        // Add dependent like and saved relationships on User A's posts
        QuoteLike::create(['user_id' => $userB->id, 'quote_id' => $quoteA2->id]);
        SavedQuote::create(['user_id' => $userB->id, 'quote_id' => $quoteA2->id]);

        // 1. User B cannot delete User A's post
        $this->actingAs($userB)
            ->delete(route('my-quotes.destroy', $quoteA1))
            ->assertForbidden();
        $this->assertDatabaseHas('quotes', ['id' => $quoteA1->id]);

        // 2. User A deletes a single post ($quoteA1)
        $this->actingAs($userA)
            ->from(route('my-quotes.index'))
            ->delete(route('my-quotes.destroy', $quoteA1))
            ->assertRedirect(route('my-quotes.index'));
        $this->assertDatabaseMissing('quotes', ['id' => $quoteA1->id]);

        // 3. User A executes Delete All My Posts (removes $quoteA2 and $quoteA3 and their likes/saves, keeps $quoteB1)
        $this->actingAs($userA)
            ->delete(route('my-quotes.destroy-all'))
            ->assertRedirect(route('my-quotes.index'));

        $this->assertDatabaseMissing('quotes', ['id' => $quoteA2->id]);
        $this->assertDatabaseMissing('quotes', ['id' => $quoteA3->id]);
        $this->assertDatabaseCount('quote_likes', 0);
        $this->assertDatabaseCount('saved_quotes', 0);

        // User B's quote and both user accounts remain intact
        $this->assertDatabaseHas('quotes', ['id' => $quoteB1->id]);
        $this->assertDatabaseHas('users', ['id' => $userA->id]);
        $this->assertDatabaseHas('users', ['id' => $userB->id]);
    }
}