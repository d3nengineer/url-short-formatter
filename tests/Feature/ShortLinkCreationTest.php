<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ShortLinkCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Str::createRandomStringsNormally();

        parent::tearDown();
    }

    public function test_guests_are_redirected_from_short_link_creation(): void
    {
        $response = $this->post('/short-links', [
            'original_url' => 'https://example.com/article',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_create_a_short_link_and_see_the_generated_url(): void
    {
        Str::createRandomStringsUsing(fn (int $length): string => $length === 8 ? 'abc123xy' : str_repeat('x', $length));
        $user = User::factory()->create();

        $response = $this->actingAs($user)->followingRedirects()->post('/short-links', [
            'original_url' => ' https://example.com/articles/laravel ',
        ]);

        $response->assertOk();
        $response->assertSee(url('abc123xy'));

        $this->assertDatabaseHas('short_links', [
            'user_id' => $user->id,
            'original_url' => 'https://example.com/articles/laravel',
            'slug' => 'abc123xy',
        ]);

        $this->assertNotEmpty(ShortLink::query()->firstOrFail()->slug);
    }

    public function test_original_url_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/short-links', [
            'original_url' => '',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('original_url');
    }

    public function test_original_url_must_be_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/short-links', [
            'original_url' => 'not-a-url',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('original_url');
    }

    public function test_original_url_must_use_http_or_https(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/short-links', [
            'original_url' => 'ftp://example.com/file',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('original_url');
    }

    public function test_original_url_must_not_be_too_long(): void
    {
        $user = User::factory()->create();
        $tooLongUrl = 'https://example.com/'.str_repeat('a', 2030);

        $response = $this->actingAs($user)->from('/dashboard')->post('/short-links', [
            'original_url' => $tooLongUrl,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('original_url');
    }

    public function test_slug_collision_retries_until_a_unique_slug_is_available(): void
    {
        $user = User::factory()->create();

        ShortLink::factory()->create([
            'slug' => 'collide1',
        ]);

        $slugs = ['collide1', 'fresh123'];

        Str::createRandomStringsUsing(function (int $length) use (&$slugs): string {
            if ($length === 8) {
                return array_shift($slugs) ?? 'backup12';
            }

            return str_repeat('x', $length);
        });

        $response = $this->actingAs($user)->post('/short-links', [
            'original_url' => 'https://example.com/collision-test',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('short_link.slug', 'fresh123');

        $this->assertDatabaseHas('short_links', [
            'user_id' => $user->id,
            'original_url' => 'https://example.com/collision-test',
            'slug' => 'fresh123',
        ]);
    }
}
