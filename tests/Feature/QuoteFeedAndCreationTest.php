<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteFeedAndCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_both_quote_types_and_copy_action_is_rendered(): void
    {
        $publisher = User::factory()->create(['name' => 'أحمد القارئ']);

        $quickQuote = Quote::factory()->create([
            'user_id' => $publisher->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'الكتب منارات تهدي العقول في دروب المعرفة.',
        ]);

        $bookQuote = Quote::factory()->book()->create([
            'user_id' => $publisher->id,
            'quote_text' => 'Knowledge is the life of the mind.',
            'book_title' => 'Classical Wisdom',
            'attributed_author' => 'Al-Jahiz',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($quickQuote->quote_text);
        $response->assertSee($bookQuote->quote_text);
        $response->assertSee('Classical Wisdom');
        $response->assertSee('Al-Jahiz');
        $response->assertSee('أحمد القارئ');
        $response->assertSee('data-copy-quote', false);
    }

    public function test_homepage_filters_by_quote_type_and_searches_across_all_fields(): void
    {
        $publisherA = User::factory()->create(['name' => 'سارة الأدبية']);
        $publisherB = User::factory()->create(['name' => 'Omar Khaled']);

        $quick = Quote::factory()->create([
            'user_id' => $publisherA->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'تأملات صباحية في حب القراءة والهدوء.',
        ]);

        $book = Quote::factory()->book()->create([
            'user_id' => $publisherB->id,
            'quote_text' => 'The only true wisdom is in knowing you know nothing.',
            'book_title' => 'Apology of Socrates',
            'attributed_author' => 'Socrates',
        ]);

        // Filter: Quick quotes only
        $quickFilter = $this->get(route('home', ['type' => 'quick']));
        $quickFilter->assertStatus(200);
        $quickFilter->assertSee($quick->quote_text);
        $quickFilter->assertDontSee($book->quote_text);

        // Filter: Book quotes only
        $bookFilter = $this->get(route('home', ['type' => 'book']));
        $bookFilter->assertStatus(200);
        $bookFilter->assertSee($book->quote_text);
        $bookFilter->assertDontSee($quick->quote_text);

        // Search by book title
        $searchBook = $this->get(route('home', ['q' => 'Apology']));
        $searchBook->assertSee($book->quote_text);
        $searchBook->assertDontSee($quick->quote_text);

        // Search by attributed author
        $searchAuthor = $this->get(route('home', ['q' => 'Socrates']));
        $searchAuthor->assertSee($book->quote_text);
        $searchAuthor->assertDontSee($quick->quote_text);

        // Search by platform publisher name
        $searchPublisher = $this->get(route('home', ['q' => 'سارة']));
        $searchPublisher->assertSee($quick->quote_text);
        $searchPublisher->assertDontSee($book->quote_text);
    }

    public function test_guests_cannot_access_quote_creation_routes(): void
    {
        $this->get(route('quotes.create-quick'))->assertRedirect(route('login'));
        $this->post(route('quotes.store-quick'), ['quote_text' => 'Valid length quote'])
            ->assertRedirect(route('login'));

        $this->get(route('quotes.create-book'))->assertRedirect(route('login'));
        $this->post(route('quotes.store-book'), [])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_valid_quick_quote_and_invalid_lengths_are_rejected(): void
    {
        $user = User::factory()->create();

        // Valid quick quote (3-155 chars)
        $response = $this->actingAs($user)->post(route('quotes.store-quick'), [
            'quote_text' => 'الحكمة ضالة المؤمن أنى وجدها فهو أحق بها.',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('quotes', [
            'user_id' => $user->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'الحكمة ضالة المؤمن أنى وجدها فهو أحق بها.',
        ]);

        // Too short (< 3 chars)
        $shortResponse = $this->actingAs($user)
            ->from(route('quotes.create-quick'))
            ->post(route('quotes.store-quick'), [
                'quote_text' => 'أب',
            ]);
        $shortResponse->assertRedirect(route('quotes.create-quick'));
        $shortResponse->assertSessionHasErrors(['quote_text']);

        // Too long (> 155 chars)
        $longResponse = $this->actingAs($user)
            ->from(route('quotes.create-quick'))
            ->post(route('quotes.store-quick'), [
                'quote_text' => str_repeat('ك', 156),
            ]);
        $longResponse->assertRedirect(route('quotes.create-quick'));
        $longResponse->assertSessionHasErrors(['quote_text']);
    }

    public function test_authenticated_user_can_create_book_quote_with_four_required_fields_and_cover_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'ناشر المنصة']);

        $coverFile = UploadedFile::fake()->image('muqaddimah.jpg', 500, 750);

        $response = $this->actingAs($user)->post(route('quotes.store-book'), [
            'book_title' => 'مقدمة ابن خلدون',
            'book_cover' => $coverFile,
            'quote_text' => 'الماضي أشبه بالآتي من الماء بالماء.',
            'attributed_author' => 'عبد الرحمن بن خلدون',
        ]);

        $response->assertRedirect(route('home'));

        $quote = Quote::where('book_title', 'مقدمة ابن خلدون')->firstOrFail();
        $this->assertSame($user->id, $quote->user_id);
        $this->assertSame(Quote::TYPE_BOOK, $quote->type);
        $this->assertSame('عبد الرحمن بن خلدون', $quote->attributed_author);
        $this->assertNotSame($quote->user->name, $quote->attributed_author);
        $this->assertNotNull($quote->book_cover_path);

        Storage::disk('public')->assertExists($quote->book_cover_path);
    }

    public function test_book_quote_submission_fails_if_any_of_the_four_mandatory_fields_is_missing_or_invalid(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Missing all 4 fields
        $emptyResponse = $this->actingAs($user)
            ->from(route('quotes.create-book'))
            ->post(route('quotes.store-book'), []);

        $emptyResponse->assertRedirect(route('quotes.create-book'));
        $emptyResponse->assertSessionHasErrors([
            'book_title',
            'book_cover',
            'quote_text',
            'attributed_author',
        ]);

        // Non-image file uploaded as cover
        $invalidFile = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');
        $badFileResponse = $this->actingAs($user)
            ->from(route('quotes.create-book'))
            ->post(route('quotes.store-book'), [
                'book_title' => 'كتاب تجريبي',
                'book_cover' => $invalidFile,
                'quote_text' => 'نص اقتباس صالح للاختبار.',
                'attributed_author' => 'مؤلف الكتاب',
            ]);

        $badFileResponse->assertSessionHasErrors(['book_cover']);
    }
}