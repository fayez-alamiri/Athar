<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalRefinementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_quotes_display_arabic_and_english_author_attribution_underneath_and_handle_null_gracefully(): void
    {
        $publisher = User::factory()->create(['name' => 'ناشر المنصة']);

        $arabicQuickWithAuthor = Quote::factory()->create([
            'user_id' => $publisher->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => "على قدر أهل العزم تأتي العزائم\nوتأتي على قدر الكرام المكارم",
            'attributed_author' => 'المتنبي',
        ]);

        $englishQuickWithAuthor = Quote::factory()->create([
            'user_id' => $publisher->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'The only true wisdom is in knowing you know nothing.',
            'attributed_author' => 'سقراط',
        ]);

        $quickWithoutAuthor = Quote::factory()->create([
            'user_id' => $publisher->id,
            'type' => Quote::TYPE_QUICK,
            'quote_text' => 'خاطرة أدبية بدون مؤلف منسوب.',
            'attributed_author' => null,
        ]);

        $response = $this->get(route('home'));
        $response->assertOk();

        // Arabic quick quote uses «...» and shows "— المتنبي"
        $response->assertSee('«على قدر أهل العزم تأتي العزائم');
        $response->assertSee('— المتنبي');
        $response->assertSee('data-quick-quote-attribution="' . $arabicQuickWithAuthor->id . '"', false);

        // English quick quote uses “...” and shows "— سقراط"
        $response->assertSee('“The only true wisdom is in knowing you know nothing.”');
        $response->assertSee('— سقراط');
        $response->assertSee('data-quick-quote-attribution="' . $englishQuickWithAuthor->id . '"', false);

        // Quick quote without attributed_author does NOT render an empty attribution line
        $response->assertDontSee('data-quick-quote-attribution="' . $quickWithoutAuthor->id . '"', false);
    }

    public function test_book_cover_url_resolves_public_images_books_paths_and_storage_paths_correctly(): void
    {
        $user = User::factory()->create();

        // 1. Public seeded path: images/books/wahy-al-qalam.jfif -> /images/books/wahy-al-qalam.jfif
        $seededQuote = Quote::factory()->book()->create([
            'user_id' => $user->id,
            'book_title' => 'وحي القلم',
            'book_cover_path' => 'images/books/wahy-al-qalam.jfif',
        ]);
        $this->assertSame('/images/books/wahy-al-qalam.jfif', $seededQuote->bookCoverUrl());

        // 2. Accidental "public/" prefix is stripped cleanly
        $prefixedQuote = Quote::factory()->book()->create([
            'user_id' => $user->id,
            'book_title' => 'صحيح البخاري',
            'book_cover_path' => 'public/images/books/sahih-al-bukhari.jfif',
        ]);
        $this->assertSame('/images/books/sahih-al-bukhari.jfif', $prefixedQuote->bookCoverUrl());

        // 3. Uploaded storage disk path: book-covers/uuid.jpg -> /storage/book-covers/uuid.jpg
        $uploadedQuote = Quote::factory()->book()->create([
            'user_id' => $user->id,
            'book_title' => 'Uploaded Book',
            'book_cover_path' => 'book-covers/sample.jpg',
        ]);
        $this->assertSame('/storage/book-covers/sample.jpg', $uploadedQuote->bookCoverUrl());
    }

    public function test_database_seeder_updates_existing_records_and_does_not_duplicate_when_rerun(): void
    {
        $this->seed(DatabaseSeeder::class);
        $initialCount = Quote::count();
        $this->assertGreaterThanOrEqual(8, $initialCount);

        // Verify all 4 seeded book covers are stored and rendered
        $expectedCovers = [
            'images/books/wahy-al-qalam.jfif',
            'images/books/three-fundamental-principles.jfif',
            'images/books/sahih-al-bukhari.jfif',
            'images/books/sahih-muslim.jfif',
        ];

        foreach ($expectedCovers as $coverPath) {
            $this->assertDatabaseHas('quotes', [
                'type' => Quote::TYPE_BOOK,
                'book_cover_path' => $coverPath,
            ]);
        }

        // Re-running the seeder must not create duplicates
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($initialCount, Quote::count());

        // Homepage renders the resolved /images/books/*.jfif URLs
        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('src="/images/books/wahy-al-qalam.jfif"', false);
        $home->assertSee('src="/images/books/three-fundamental-principles.jfif"', false);
        $home->assertSee('src="/images/books/sahih-al-bukhari.jfif"', false);
        $home->assertSee('src="/images/books/sahih-muslim.jfif"', false);
    }
}