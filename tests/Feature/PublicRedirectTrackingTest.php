<?php

namespace Tests\Feature;

use App\Models\RedirectClick;
use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicRedirectTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_slug_redirects_to_original_url_and_records_click(): void
    {
        $shortLink = ShortLink::factory()->create([
            'original_url' => 'https://example.com/articles/laravel?utm=private',
            'slug' => 'go_here',
        ]);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->withHeader('User-Agent', 'Feature Test Browser')
            ->get('/go_here');

        $response->assertRedirect('https://example.com/articles/laravel?utm=private');

        $this->assertDatabaseHas('redirect_clicks', [
            'short_link_id' => $shortLink->id,
            'visitor_ip' => '203.0.113.10',
            'user_agent' => 'Feature Test Browser',
        ]);

        $this->assertNotNull($shortLink->fresh()->last_redirected_at);
    }

    public function test_unknown_slug_returns_not_found_without_recording_click(): void
    {
        $response = $this->get('/unknown_slug');

        $response->assertNotFound();
        $this->assertDatabaseCount('redirect_clicks', 0);
    }

    public function test_expired_slug_returns_not_found_without_recording_click(): void
    {
        $shortLink = ShortLink::factory()->expired()->create([
            'slug' => 'expired1',
        ]);

        $response = $this->get('/expired1');

        $response->assertNotFound();
        $this->assertSame(0, $shortLink->redirectClicks()->count());
        $this->assertNull($shortLink->fresh()->last_redirected_at);
    }

    public function test_disabled_slug_returns_not_found_without_recording_click(): void
    {
        $shortLink = ShortLink::factory()->disabled()->create([
            'slug' => 'disabled1',
        ]);

        $response = $this->get('/disabled1');

        $response->assertNotFound();
        $this->assertSame(0, $shortLink->redirectClicks()->count());
        $this->assertNull($shortLink->fresh()->last_redirected_at);
    }

    public function test_soft_deleted_slug_returns_not_found_without_recording_click(): void
    {
        $shortLink = ShortLink::factory()->create([
            'slug' => 'deleted1',
        ]);

        $shortLink->delete();

        $response = $this->get('/deleted1');

        $response->assertNotFound();
        $this->assertDatabaseCount('redirect_clicks', 0);
        $this->assertNull(ShortLink::withTrashed()->findOrFail($shortLink->id)->last_redirected_at);
    }

    public function test_existing_application_routes_take_precedence_over_public_slug_route(): void
    {
        ShortLink::factory()->create([
            'slug' => 'dashboard',
        ]);

        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/logout')->assertRedirect('/login');

        $this->assertDatabaseCount('redirect_clicks', 0);
    }

    public function test_redirect_clicks_belong_to_short_links(): void
    {
        $shortLink = ShortLink::factory()->create();
        $redirectClick = RedirectClick::query()->create([
            'short_link_id' => $shortLink->id,
            'visitor_ip' => '203.0.113.20',
            'user_agent' => null,
        ]);

        $this->assertTrue($redirectClick->shortLink->is($shortLink));
        $this->assertTrue($shortLink->redirectClicks()->firstOrFail()->is($redirectClick));
    }
}
