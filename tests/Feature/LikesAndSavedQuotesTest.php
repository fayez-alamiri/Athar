<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteLike;
use App\Models\SavedQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikesAndSavedQuotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_like_or_save_quotes_or_view_saved_quotes_page(): void
    {
        $quote = Quote::factory()->create();

        $this->post(route('quotes.like.toggle', $quote))
            ->assertRedirect(route('login'));

        $this->post(route('quotes.save.toggle', $quote))
            ->assertRedirect(route('login'));

        $this->get(route('saved.index'))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('quote_likes', 0);
        $this->assertDatabaseCount('saved_quotes', 0);

        // Verify guest homepage renders sign-in prompt triggers for Like, Save, and Saved Quotes
        $home = $this->get(route('home'));
        $home->assertStatus(200);
        $home->assertSee('data-require-auth="like"', false);
        $home->assertSee('data-require-auth="save"', false);
        $home->assertSee('data-require-auth="saved"', false);
    }

    public function test_authenticated_user_can_like_unlike_and_like_again_with_accurate_counts(): void
    {
        $author = User::factory()->create();
        $reader = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $author->id]);

        // 1. Like the quote via JSON/AJAX request
        $likeResponse = $this->actingAs($reader)
            ->postJson(route('quotes.like.toggle', $quote));

        $likeResponse->assertOk()
            ->assertJson([
                'quote_id' => $quote->id,
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->assertDatabaseHas('quote_likes', [
            'user_id' => $reader->id,
            'quote_id' => $quote->id,
        ]);
        $this->assertSame(1, $author->receivedLikesCount());

        // 2. Unlike the quote
        $unlikeResponse = $this->actingAs($reader)
            ->postJson(route('quotes.like.toggle', $quote));

        $unlikeResponse->assertOk()
            ->assertJson([
                'quote_id' => $quote->id,
                'liked' => false,
                'likes_count' => 0,
            ]);

        $this->assertDatabaseMissing('quote_likes', [
            'user_id' => $reader->id,
            'quote_id' => $quote->id,
        ]);
        $this->assertSame(0, $author->receivedLikesCount());

        // 3. Like the quote again via standard form POST
        $reLikeResponse = $this->actingAs($reader)
            ->from(route('home'))
            ->post(route('quotes.like.toggle', $quote));

        $reLikeResponse->assertRedirect(route('home'));
        $this->assertSame(1, QuoteLike::where('user_id', $reader->id)->where('quote_id', $quote->id)->count());
    }

    public function test_authenticated_user_can_save_own_and_others_quotes_without_duplicating_quote_or_changing_owner(): void
    {
        $userA = User::factory()->create(['name' => 'Owner A']);
        $userB = User::factory()->create(['name' => 'Owner B']);

        $ownQuickQuote = Quote::factory()->create([
            'user_id' => $userA->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'اقتباس خاص بالمستخدم الأول.',
        ]);

        $otherBookQuote = Quote::factory()->book()->create([
            'user_id' => $userB->id,
            'quote_text' => 'A classic book passage shared by User B.',
            'book_title' => 'Timeless Letters',
            'attributed_author' => 'Ibn Hazm',
        ]);

        // User A saves their own quick quote
        $this->actingAs($userA)
            ->postJson(route('quotes.save.toggle', $ownQuickQuote))
            ->assertOk()
            ->assertJson(['saved' => true]);

        // User A saves User B's book quote
        $this->actingAs($userA)
            ->postJson(route('quotes.save.toggle', $otherBookQuote))
            ->assertOk()
            ->assertJson(['saved' => true]);

        // Saving must NOT create duplicate Quote records or change user_id ownership
        $this->assertDatabaseCount('quotes', 2);
        $this->assertSame($userB->id, $otherBookQuote->fresh()->user_id);
        $this->assertDatabaseCount('saved_quotes', 2);

        // Unsaving removes only the SavedQuote relationship
        $this->actingAs($userA)
            ->postJson(route('quotes.save.toggle', $ownQuickQuote))
            ->assertOk()
            ->assertJson(['saved' => false]);

        $this->assertDatabaseMissing('saved_quotes', [
            'user_id' => $userA->id,
            'quote_id' => $ownQuickQuote->id,
        ]);
        $this->assertDatabaseHas('quotes', ['id' => $ownQuickQuote->id]);
    }

    public function test_saved_quotes_page_lists_only_current_users_saved_quotes_and_supports_filtering(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $savedQuick = Quote::factory()->create([
            'user_id' => $userB->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'اقتباس سريع محفوظ لدى المستخدم الأول.',
        ]);

        $savedBook = Quote::factory()->book()->create([
            'user_id' => $userB->id,
            'quote_text' => 'Saved book quote for User A.',
            'book_title' => 'Book of Reflections',
            'attributed_author' => 'Al-Tawhidi',
        ]);

        $otherUsersSavedQuote = Quote::factory()->create([
            'user_id' => $userA->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'هذا الاقتباس محفوظ لدى المستخدم الثاني فقط.',
        ]);

        SavedQuote::create(['user_id' => $userA->id, 'quote_id' => $savedQuick->id]);
        SavedQuote::create(['user_id' => $userA->id, 'quote_id' => $savedBook->id]);
        SavedQuote::create(['user_id' => $userB->id, 'quote_id' => $otherUsersSavedQuote->id]);

        // User A views their private Saved Quotes (المحفوظات) page
        $response = $this->actingAs($userA)->get(route('saved.index'));
        $response->assertOk();
        $response->assertSee('المحفوظات');
        $response->assertSee($savedQuick->quote_text);
        $response->assertSee($savedBook->quote_text);
        $response->assertDontSee($otherUsersSavedQuote->quote_text);

        // Filter Saved Quotes by 'book' type
        $bookFilter = $this->actingAs($userA)->get(route('saved.index', ['type' => 'book']));
        $bookFilter->assertOk();
        $bookFilter->assertSee($savedBook->quote_text);
        $bookFilter->assertDontSee($savedQuick->quote_text);
    }
}