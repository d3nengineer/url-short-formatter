<?php

namespace Tests\Feature;

use App\Models\RedirectClick;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

final class UserLinkDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_user_link_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_lists_only_authenticated_users_links_newest_first(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $olderLink = ShortLink::factory()->for($user)->create([
            'original_url' => 'https://example.com/older?private=1',
            'slug' => 'older1',
            'created_at' => now()->subDay(),
        ]);
        $newerLink = ShortLink::factory()->for($user)->redirected()->create([
            'original_url' => 'https://example.com/newer',
            'slug' => 'newer1',
            'created_at' => now(),
        ]);
        $otherLink = ShortLink::factory()->for($otherUser)->create([
            'original_url' => 'https://other.example.com/hidden',
            'slug' => 'hidden1',
        ]);

        RedirectClick::query()->create([
            'short_link_id' => $newerLink->id,
            'visitor_ip' => '203.0.113.1',
            'user_agent' => 'First Browser',
        ]);
        RedirectClick::query()->create([
            'short_link_id' => $newerLink->id,
            'visitor_ip' => '203.0.113.2',
            'user_agent' => 'Second Browser',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(url('newer1'));
        $response->assertSee(url('older1'));
        $response->assertSee('https://example.com/newer');
        $response->assertSee('https://example.com/older?private=1');
        $response->assertSee('2');
        $response->assertSee('Active');
        $response->assertDontSee(url('hidden1'));
        $response->assertDontSee($otherLink->original_url);
        $response->assertSeeInOrder([
            url($newerLink->slug),
            url($olderLink->slug),
        ]);
    }

    public function test_dashboard_shows_empty_state_when_user_has_no_links(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('No short links yet. Create your first link above.');
    }

    public function test_dashboard_route_takes_precedence_over_public_slug_redirect(): void
    {
        ShortLink::factory()->create([
            'slug' => 'dashboard',
        ]);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertDatabaseCount('redirect_clicks', 0);
    }

    public function test_owner_can_soft_delete_short_link_from_dashboard(): void
    {
        Log::spy();

        $user = User::factory()->create();
        $shortLink = ShortLink::factory()->for($user)->create([
            'slug' => 'delete1',
        ]);

        $response = $this->actingAs($user)->delete(route('short-links.destroy', $shortLink));

        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas('status', 'Short link deleted.');
        $this->assertSoftDeleted('short_links', [
            'id' => $shortLink->id,
        ]);

        Log::shouldHaveReceived('info')
            ->with('Short link soft deleted successfully.', Mockery::on(
                fn (array $context): bool => $context['user_id'] === $user->id
                    && $context['short_link_id'] === $shortLink->id
                    && $context['slug'] === 'delete1',
            ))
            ->once();
    }

    public function test_guests_are_redirected_to_login_when_deleting_short_link(): void
    {
        $shortLink = ShortLink::factory()->create([
            'slug' => 'guestdel',
        ]);

        $response = $this->delete(route('short-links.destroy', $shortLink));

        $response->assertRedirect('/login');
        $this->assertNotSoftDeleted('short_links', [
            'id' => $shortLink->id,
        ]);
    }

    public function test_non_owner_cannot_delete_short_link(): void
    {
        Log::spy();

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $shortLink = ShortLink::factory()->for($owner)->create([
            'slug' => 'owned1',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('short-links.destroy', $shortLink));

        $response->assertNotFound();
        $this->assertNotSoftDeleted('short_links', [
            'id' => $shortLink->id,
        ]);

        Log::shouldHaveReceived('warning')
            ->with('Unauthorized short link deletion attempt denied.', Mockery::on(
                fn (array $context): bool => $context['user_id'] === $otherUser->id
                    && $context['short_link_id'] === $shortLink->id
                    && $context['owner_id'] === $owner->id,
            ))
            ->once();
    }

    public function test_soft_deleted_link_is_removed_from_dashboard_and_public_redirects(): void
    {
        $user = User::factory()->create();
        $shortLink = ShortLink::factory()->for($user)->create([
            'original_url' => 'https://example.com/delete-me',
            'slug' => 'gone1',
        ]);

        $this->actingAs($user)->delete(route('short-links.destroy', $shortLink));

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee(url('gone1'))
            ->assertDontSee('https://example.com/delete-me');

        $this->get('/gone1')->assertNotFound();
        $this->assertDatabaseCount('redirect_clicks', 0);
    }
}
