<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareQuoteGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_access_png_share_modal_and_all_four_templates_without_signing_in(): void
    {
        $publisher = User::factory()->create(['name' => 'ناشر الأدب']);

        $arabicQuickQuote = Quote::factory()->create([
            'user_id' => $publisher->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'الكتاب صديق لا يخون، ورفيق لا يمل.',
        ]);

        $englishBookQuote = Quote::factory()->book()->create([
            'user_id' => $publisher->id,
            'quote_text' => 'There is no friend as loyal as a book.',
            'book_title' => 'Selected Essays',
            'attributed_author' => 'Ernest Hemingway',
            'book_cover_path' => 'book-covers/hemingway.jpg',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();

        // Share modal, canvas, and inline share bootstrapper are rendered for guests
        $response->assertSee('id="share-quote-modal"', false);
        $response->assertSee('id="share-quote-canvas"', false);
        $response->assertSee('window.__QUOTEIT_SHARE_BOOTSTRAPPED__', false);

        // All 4 selectable templates exist
        $response->assertSee('data-share-template="editorial"', false);
        $response->assertSee('data-share-template="paper"', false);
        $response->assertSee('data-share-template="dark"', false);
        $response->assertSee('data-share-template="magazine"', false);

        // Export and fallback buttons exist
        $response->assertSee('id="share-download-png-btn"', false);
        $response->assertSee('id="share-native-file-btn"', false);
        $response->assertSee('id="share-copy-text-btn"', false);

        // Centralized brand name is passed to the generator
        $response->assertSee('data-brand-name="' . e(config('brand.name.ar')) . '"', false);

        // Both quick quote and book quote expose accurate data attributes and relative same-origin cover path
        $response->assertSee('data-share-quote="' . $arabicQuickQuote->id . '"', false);
        $response->assertSee('data-quote-text="' . e($arabicQuickQuote->quote_text) . '"', false);
        $response->assertSee('data-share-quote="' . $englishBookQuote->id . '"', false);
        $response->assertSee('data-quote-book="Selected Essays"', false);
        $response->assertSee('data-quote-author="Ernest Hemingway"', false);
        $response->assertSee('data-quote-cover="/storage/book-covers/hemingway.jpg"', false);
    }

    public function test_share_modal_localizes_labels_in_english_and_reflects_configured_brand_name(): void
    {
        config(['brand.name.en' => 'CustomLiteraryBrand']);

        $response = $this->withSession(['locale' => 'en'])->get(route('home'));

        $response->assertOk();
        $response->assertSee('Share Quote as PNG');
        $response->assertSee('Warm Editorial');
        $response->assertSee('Quiet Paper');
        $response->assertSee('Dark Elegant');
        $response->assertSee('Literary Magazine');
        $response->assertSee('Download PNG');
        $response->assertSee('Copy Quote Text');
        $response->assertSee('data-brand-name="CustomLiteraryBrand"', false);
    }
}