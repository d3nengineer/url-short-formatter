<?php

namespace App\Actions\ShortLinks;

use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class ResolveShortLinkRedirectAction
{
    public function handle(string $slug, Request $request): ResolveShortLinkRedirectResult
    {
        try {
            $result = DB::transaction(function () use ($request, $slug): ResolveShortLinkRedirectResult {
                $shortLink = ShortLink::withTrashed()
                    ->where('slug', $slug)
                    ->lockForUpdate()
                    ->first();

                if ($shortLink === null) {
                    return ResolveShortLinkRedirectResult::unavailable('missing', slug: $slug);
                }

                $unavailableReason = $shortLink->redirectUnavailableReason();

                if ($unavailableReason !== null) {
                    return ResolveShortLinkRedirectResult::unavailable(
                        $unavailableReason,
                        $shortLink->id,
                        $shortLink->slug,
                    );
                }

                $shortLink->redirectClicks()->create([
                    'visitor_ip' => (string) $request->ip(),
                    'user_agent' => $this->safeUserAgent($request),
                ]);

                $shortLink->forceFill([
                    'last_redirected_at' => now(),
                ])->save();

                return ResolveShortLinkRedirectResult::available(
                    $shortLink->original_url,
                    $shortLink->id,
                    $shortLink->slug,
                );
            });
        } catch (Throwable $exception) {
            Log::error('Short link redirect persistence failed.', [
                'slug' => $slug,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }

        if (! $result->available) {
            Log::warning('Short link redirect unavailable.', [
                'short_link_id' => $result->shortLinkId,
                'slug' => $result->slug,
                'reason' => $result->reason,
            ]);

            return $result;
        }

        Log::info('Short link redirect resolved successfully.', [
            'short_link_id' => $result->shortLinkId,
            'slug' => $result->slug,
        ]);

        return $result;
    }

    private function safeUserAgent(Request $request): ?string
    {
        $userAgent = $request->userAgent();

        if ($userAgent === null) {
            return null;
        }

        return Str::substr($userAgent, 0, 512);
    }
}
