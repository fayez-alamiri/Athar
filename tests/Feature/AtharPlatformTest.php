<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteOfTheDayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtharPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_discovery_pages_are_available(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('authors.index'))->assertOk();
        $this->get(route('quotes.daily'))->assertOk();
        $this->get(route('quotes.zen'))->assertOk();
        $this->get(route('health'))->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_quote_catalog_sync_creates_author_and_book_relations(): void
    {
        $publisher = User::factory()->create();
        $quote = Quote::factory()->book()->create([
            'user_id' => $publisher->id,
            'book_title' => 'The Example Book',
            'attributed_author' => 'A. N. Writer',
            'book_cover_path' => 'images/books/example.jpg',
        ]);

        $quote->syncCatalogRelations();
        $quote->refresh();

        $this->assertNotNull($quote->author_id);
        $this->assertNotNull($quote->book_id);
        $this->assertSame('A. N. Writer', $quote->author->name);
        $this->assertSame('The Example Book', $quote->book->title);
        $this->assertDatabaseHas('authors', ['name' => 'A. N. Writer']);
        $this->assertDatabaseHas('books', ['title' => 'The Example Book']);
    }

    public function test_author_directory_and_author_detail_show_related_quotes(): void
    {
        $publisher = User::factory()->create();
        $quote = Quote::factory()->create([
            'user_id' => $publisher->id,
            'attributed_author' => 'Naguib Mahfouz',
        ]);
        $quote->syncCatalogRelations();
        $author = Author::query()->where('name', 'Naguib Mahfouz')->firstOrFail();

        $this->get(route('authors.index'))
            ->assertOk()
            ->assertSee('Naguib Mahfouz');

        $this->get(route('authors.show', $author))
            ->assertOk()
            ->assertSee('Naguib Mahfouz')
            ->assertSee($quote->quote_text);
    }

    public function test_quote_of_the_day_persists_one_selection_for_the_current_riyadh_date(): void
    {
        $publisher = User::factory()->create();
        Quote::factory()->count(4)->create(['user_id' => $publisher->id]);

        $service = app(QuoteOfTheDayService::class);
        $first = $service->forToday();
        $second = $service->forToday();

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second?->id);
        $this->assertDatabaseCount('daily_quotes', 1);
        $this->assertDatabaseHas('daily_quotes', [
            'quote_date' => now('Asia/Riyadh')->toDateString(),
            'quote_id' => $first->id,
        ]);
    }

    public function test_zen_page_displays_one_quote_and_keyboard_navigation_controls(): void
    {
        $publisher = User::factory()->create();
        Quote::factory()->count(2)->create(['user_id' => $publisher->id]);

        $this->get(route('quotes.zen'))
            ->assertOk()
            ->assertSee('data-zen-mode', false)
            ->assertSee('data-zen-next', false)
            ->assertSee('data-zen-exit', false);
    }
}
