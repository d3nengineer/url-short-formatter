<?php

namespace Tests\Feature;

use App\Models\RedirectClick;
use App\Models\ShortLink;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClickStatisticsStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_redirect_records_queryable_click_timestamp(): void
    {
        $clickedAt = now()->startOfSecond();
        $shortLink = ShortLink::factory()->create([
            'slug' => 'stats_one',
        ]);

        $this->travelTo($clickedAt);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.30'])
            ->withHeader('User-Agent', str_repeat('A', 600))
            ->get('/stats_one')
            ->assertRedirect($shortLink->original_url);

        $redirectClick = RedirectClick::query()->firstOrFail();

        $this->assertSame($shortLink->id, $redirectClick->short_link_id);
        $this->assertSame('203.0.113.30', $redirectClick->visitor_ip);
        $this->assertSame(str_repeat('A', 512), $redirectClick->user_agent);
        $this->assertInstanceOf(CarbonInterface::class, $redirectClick->created_at);
        $this->assertTrue($redirectClick->created_at->equalTo($clickedAt));
        $this->assertTrue($shortLink->fresh()->last_redirected_at->equalTo($clickedAt));
    }

    public function test_redirect_click_scopes_filter_by_link_date_window_and_newest_order(): void
    {
        $shortLink = ShortLink::factory()->create();
        $otherShortLink = ShortLink::factory()->create();
        $olderClickAt = now()->subDays(2);
        $middleClickAt = now()->subDay();
        $newerClickAt = now();

        RedirectClick::query()->create([
            'short_link_id' => $shortLink->id,
            'visitor_ip' => '203.0.113.31',
            'user_agent' => null,
            'created_at' => $olderClickAt,
            'updated_at' => $olderClickAt,
        ]);

        RedirectClick::query()->create([
            'short_link_id' => $shortLink->id,
            'visitor_ip' => '203.0.113.32',
            'user_agent' => 'Feature Test Browser',
            'created_at' => $middleClickAt,
            'updated_at' => $middleClickAt,
        ]);

        RedirectClick::query()->create([
            'short_link_id' => $shortLink->id,
            'visitor_ip' => '203.0.113.33',
            'user_agent' => 'Feature Test Browser',
            'created_at' => $newerClickAt,
            'updated_at' => $newerClickAt,
        ]);

        RedirectClick::query()->create([
            'short_link_id' => $otherShortLink->id,
            'visitor_ip' => '203.0.113.34',
            'user_agent' => 'Other Link Browser',
            'created_at' => $newerClickAt,
            'updated_at' => $newerClickAt,
        ]);

        $visitorIps = RedirectClick::query()
            ->forShortLink($shortLink)
            ->clickedBetween($olderClickAt->copy()->addMinute(), $newerClickAt)
            ->newestFirst()
            ->pluck('visitor_ip')
            ->all();

        $this->assertSame([
            '203.0.113.33',
            '203.0.113.32',
        ], $visitorIps);
    }
}
