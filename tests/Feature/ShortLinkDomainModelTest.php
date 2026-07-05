<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShortLinkDomainModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_links_can_be_persisted_for_users(): void
    {
        $user = User::factory()->create();

        $shortLink = ShortLink::factory()->for($user)->create([
            'original_url' => 'https://example.com/articles/laravel',
            'slug' => 'laravel1',
        ]);

        $this->assertDatabaseHas('short_links', [
            'id' => $shortLink->id,
            'user_id' => $user->id,
            'original_url' => 'https://example.com/articles/laravel',
            'slug' => 'laravel1',
        ]);
    }

    public function test_short_links_belong_to_users_and_users_have_short_links(): void
    {
        $user = User::factory()->create();
        $shortLink = ShortLink::factory()->for($user)->create();

        $this->assertTrue($shortLink->user->is($user));
        $this->assertTrue($user->shortLinks()->firstOrFail()->is($shortLink));
    }

    public function test_short_link_slugs_must_be_unique(): void
    {
        ShortLink::factory()->create([
            'slug' => 'shared1',
        ]);

        $this->expectException(QueryException::class);

        ShortLink::factory()->create([
            'slug' => 'shared1',
        ]);
    }

    public function test_short_links_use_slug_for_route_model_binding(): void
    {
        $shortLink = new ShortLink;

        $this->assertSame('slug', $shortLink->getRouteKeyName());
    }

    public function test_lifecycle_fields_are_cast_as_dates(): void
    {
        $shortLink = ShortLink::factory()->create([
            'expires_at' => now()->addDay(),
            'disabled_at' => now(),
            'last_redirected_at' => now()->subMinute(),
        ]);

        $this->assertInstanceOf(CarbonInterface::class, $shortLink->expires_at);
        $this->assertInstanceOf(CarbonInterface::class, $shortLink->disabled_at);
        $this->assertInstanceOf(CarbonInterface::class, $shortLink->last_redirected_at);
    }

    public function test_factory_lifecycle_states_are_available(): void
    {
        $expired = ShortLink::factory()->expired()->create();
        $disabled = ShortLink::factory()->disabled()->create();
        $redirected = ShortLink::factory()->redirected()->create();

        $this->assertInstanceOf(CarbonInterface::class, $expired->expires_at);
        $this->assertInstanceOf(CarbonInterface::class, $disabled->disabled_at);
        $this->assertInstanceOf(CarbonInterface::class, $redirected->last_redirected_at);
    }

    public function test_short_links_can_be_soft_deleted(): void
    {
        $shortLink = ShortLink::factory()->create();

        $shortLink->delete();

        $this->assertSoftDeleted('short_links', [
            'id' => $shortLink->id,
        ]);
    }
}
