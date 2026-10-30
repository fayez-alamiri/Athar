<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    /**
     * Define the model's default state (Quick Quote by default).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => Quote::TYPE_QUICK,
            'quote_text' => fake()->realTextBetween(30, 140),
            'book_title' => null,
            'book_cover_path' => null,
            'attributed_author' => null,
        ];
    }

    /**
     * Indicate that the quote is a Book Quote with all required book metadata.
     */
    public function book(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Quote::TYPE_BOOK,
            'quote_text' => fake()->realTextBetween(40, 220),
            'book_title' => fake()->sentence(3),
            'book_cover_path' => 'book-covers/sample-cover.jpg',
            'attributed_author' => fake()->name(),
        ]);
    }
}