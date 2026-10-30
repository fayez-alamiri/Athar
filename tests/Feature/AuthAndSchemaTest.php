<?php

namespace Tests\Feature;

use App\Http\Requests\StoreBookQuoteRequest;
use App\Http\Requests\StoreQuickQuoteRequest;
use App\Models\Quote;
use App\Models\QuoteLike;
use App\Models\SavedQuote;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AuthAndSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_srs_fields_and_password_is_hashed(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Fayez Reader',
            'email' => 'reader@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'reader@example.com')->firstOrFail();
        $this->assertSame('Fayez Reader', $user->name);
        $this->assertNotSame('secret1234', $user->password);
        $this->assertTrue(Hash::check('secret1234', $user->password));
    }

    public function test_registration_enforces_srs_name_email_and_password_constraints(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        // Name too short (< 2 chars), duplicate email, password < 8 chars
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'A',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();

        // Name too long (> 50 chars)
        $longNameResponse = $this->from(route('register'))->post(route('register.store'), [
            'name' => str_repeat('A', 51),
            'email' => 'new@example.com',
            'password' => 'validpass123',
            'password_confirmation' => 'validpass123',
        ]);

        $longNameResponse->assertSessionHasErrors(['name']);
    }

    public function test_user_can_sign_in_and_log_out_via_csrf_post(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->post(route('login.store'), [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_quotes_table_stores_quick_and_book_quotes_with_separate_publisher_and_attributed_author(): void
    {
        $publisher = User::factory()->create(['name' => 'Platform Publisher']);

        $quickQuote = Quote::factory()->create([
            'user_id' => $publisher->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'الكتاب نافذة تطل منها الروح على عوالم لا تحدها الجغرافيا.',
        ]);

        $bookQuote = Quote::factory()->book()->create([
            'user_id' => $publisher->id,
            'quote_text' => 'إن الكلمات لا تموت حين تُكتب بصدق.',
            'book_title' => 'رسائل الأدب العربي',
            'book_cover_path' => 'book-covers/arabic-letters.jpg',
            'attributed_author' => 'الرافعي',
        ]);

        $this->assertTrue($quickQuote->isQuickQuote());
        $this->assertNull($quickQuote->book_title);

        $this->assertTrue($bookQuote->isBookQuote());
        $this->assertSame($publisher->id, $bookQuote->user_id);
        $this->assertSame('الرافعي', $bookQuote->attributed_author);
        $this->assertNotSame($bookQuote->user->name, $bookQuote->attributed_author);
    }

    public function test_quote_policy_allows_only_post_owner_to_update_or_delete(): void
    {
        $owner = User::factory()->create(['name' => 'Post Owner']);
        $otherUser = User::factory()->create(['name' => 'Famous Author']);

        // Even if attributed_author matches $otherUser's name, only $owner owns the post
        $quote = Quote::factory()->book()->create([
            'user_id' => $owner->id,
            'attributed_author' => 'Famous Author',
        ]);

        $this->assertTrue(Gate::forUser($owner)->allows('update', $quote));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $quote));

        $this->assertFalse(Gate::forUser($otherUser)->allows('update', $quote));
        $this->assertFalse(Gate::forUser($otherUser)->allows('delete', $quote));
    }

    public function test_likes_and_saved_quotes_enforce_database_uniqueness_and_compute_received_likes(): void
    {
        $author = User::factory()->create();
        $liker = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $author->id]);

        QuoteLike::create([
            'user_id' => $liker->id,
            'quote_id' => $quote->id,
        ]);

        SavedQuote::create([
            'user_id' => $liker->id,
            'quote_id' => $quote->id,
        ]);

        $this->assertSame(1, $author->receivedLikesCount());
        $this->assertTrue($quote->isLikedBy($liker));
        $this->assertTrue($quote->isSavedBy($liker));

        // Attempting duplicate like for same (user_id, quote_id) must fail at DB level
        $this->expectException(QueryException::class);
        QuoteLike::create([
            'user_id' => $liker->id,
            'quote_id' => $quote->id,
        ]);
    }

    public function test_quick_and_book_quote_validation_rules_enforce_mandatory_constraints(): void
    {
        $quickRules = (new StoreQuickQuoteRequest())->rules();

        $tooShortQuick = Validator::make(['quote_text' => 'أب'], $quickRules);
        $this->assertTrue($tooShortQuick->fails());

        $tooLongQuick = Validator::make(['quote_text' => str_repeat('أ', 156)], $quickRules);
        $this->assertTrue($tooLongQuick->fails());

        $validQuick = Validator::make(['quote_text' => 'اقتباس قصير ملهم يحقق شروط الطول المحددة.'], $quickRules);
        $this->assertFalse($validQuick->fails());

        $bookRules = (new StoreBookQuoteRequest())->rules();

        // Missing cover and attributed_author must fail
        $incompleteBook = Validator::make([
            'book_title' => 'مقدمة ابن خلدون',
            'quote_text' => 'الإنسان مدني بالطبع.',
        ], $bookRules);
        $this->assertTrue($incompleteBook->fails());
        $this->assertArrayHasKey('book_cover', $incompleteBook->errors()->toArray());
        $this->assertArrayHasKey('attributed_author', $incompleteBook->errors()->toArray());

        // Valid 4 mandatory fields with real image passes
        $validBook = Validator::make([
            'book_title' => 'مقدمة ابن خلدون',
            'book_cover' => UploadedFile::fake()->image('cover.jpg', 400, 600),
            'quote_text' => 'الإنسان مدني بالطبع.',
            'attributed_author' => 'ابن خلدون',
        ], $bookRules);
        $this->assertFalse($validBook->fails());
    }
}