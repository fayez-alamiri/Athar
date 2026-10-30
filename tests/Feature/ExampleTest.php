<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_with_arabic_rtl_by_default(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee(config('brand.name.ar'));
    }

    public function test_language_switch_updates_session_and_direction_to_english_ltr(): void
    {
        $switchResponse = $this->from('/')->get('/locale/en');
        $switchResponse->assertRedirect('/');
        $switchResponse->assertSessionHas('locale', 'en');

        $homeResponse = $this->withSession(['locale' => 'en'])->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('lang="en"', false);
        $homeResponse->assertSee('dir="ltr"', false);
    }
}