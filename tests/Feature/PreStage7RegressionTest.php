<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PreStage7RegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dark_mode_toggle_button_and_self_contained_theme_bootstrapper_are_present(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('data-theme-icon="sun"', false);
        $response->assertSee('data-theme-icon="moon"', false);
        $response->assertSee("localStorage.getItem('quoteit_theme')", false);
        $response->assertSee('window.__QUOTEIT_BOOTSTRAPPED__', false);
    }

    public function test_like_form_uses_same_origin_relative_action_and_returns_json_without_redirect(): void
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $user->id]);

        $homeResponse = $this->actingAs($user)->get(route('home'));
        $homeResponse->assertOk();

        $expectedRelativeAction = route('quotes.like.toggle', $quote, false);
        $homeResponse->assertSee('action="' . $expectedRelativeAction . '"', false);
        $homeResponse->assertSee('data-like-form="' . $quote->id . '"', false);

        // AJAX POST returns 200 JSON (not 302 redirect) with updated state, count, and received_likes_count
        $likeJson = $this->actingAs($user)->postJson($expectedRelativeAction);
        $likeJson->assertOk()
            ->assertJson([
                'status' => 'ok',
                'quote_id' => $quote->id,
                'liked' => true,
                'likes_count' => 1,
                'received_likes_count' => 1,
            ]);

        $unlikeJson = $this->actingAs($user)->postJson($expectedRelativeAction);
        $unlikeJson->assertOk()
            ->assertJson([
                'status' => 'ok',
                'quote_id' => $quote->id,
                'liked' => false,
                'likes_count' => 0,
                'received_likes_count' => 0,
            ]);
    }

    public function test_guest_like_and_save_buttons_trigger_auth_prompt_modal_and_block_unauthenticated_requests(): void
    {
        $owner = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $owner->id]);

        // 1. Guest feed renders guest Like & Save buttons wired to guest-auth-modal and [data-require-auth]
        $guestHome = $this->get(route('home'));
        $guestHome->assertOk();
        $guestHome->assertSee('id="guest-auth-modal"', false);
        $guestHome->assertSee('data-require-auth="like"', false);
        $guestHome->assertSee('data-require-auth="save"', false);
        $guestHome->assertSee('data-modal-open="guest-auth-modal"', false);
        $guestHome->assertSee(route('login'), false);
        $guestHome->assertSee(route('register'), false);

        // 2. Unauthenticated AJAX POST to Like returns 401 Unauthorized and creates 0 records
        $this->postJson(route('quotes.like.toggle', $quote, false))
            ->assertUnauthorized();
        $this->assertDatabaseCount('quote_likes', 0);

        // 3. Unauthenticated standard POST to Like redirects to /login and creates 0 records
        $this->post(route('quotes.like.toggle', $quote, false))
            ->assertRedirect(route('login'));
        $this->assertDatabaseCount('quote_likes', 0);
    }

    public function test_navbar_avatar_menu_and_account_avatar_upload_flow_work_for_authenticated_user(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['name' => 'Fayez Alamiri']);

        // 1. Navbar renders #account-menu-button and #account-menu-dropdown
        $home = $this->actingAs($user)->get(route('home'));
        $home->assertOk();
        $home->assertSee('id="account-menu-button"', false);
        $home->assertSee('id="account-menu-dropdown"', false);
        $home->assertSee(route('account.show'));

        // 2. Account page renders interactive avatar trigger and upload form
        $accountPage = $this->actingAs($user)->get(route('account.show'));
        $accountPage->assertOk();
        $accountPage->assertSee('data-avatar-trigger', false);
        $accountPage->assertSee('id="account-avatar-form"', false);

        // 3. Uploading a valid image stores avatar and updates user record
        $file = UploadedFile::fake()->image('avatar.jpg', 250, 250);
        $upload = $this->actingAs($user)->post(route('account.avatar.update'), [
            'avatar' => $file,
        ]);

        $upload->assertRedirect(route('account.show'));
        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        // 4. Updated avatar URL is rendered in the navbar and account page
        $afterUpload = $this->actingAs($user)->get(route('account.show'));
        $afterUpload->assertOk();
        $afterUpload->assertSee($user->avatarUrl(), false);
    }
}