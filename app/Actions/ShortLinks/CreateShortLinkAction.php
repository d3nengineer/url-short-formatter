<?php

namespace App\Actions\ShortLinks;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class CreateShortLinkAction
{
    private const SLUG_LENGTH = 8;

    private const MAX_ATTEMPTS = 10;

    public function handle(User $user, string $originalUrl): ShortLink
    {
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $slug = Str::random(self::SLUG_LENGTH);

            if (ShortLink::withTrashed()->where('slug', $slug)->exists()) {
                Log::warning('Short link slug collision detected before persistence.', [
                    'attempt' => $attempt,
                    'slug' => $slug,
                ]);

                continue;
            }

            try {
                $shortLink = $user->shortLinks()->create([
                    'original_url' => $originalUrl,
                    'slug' => $slug,
                ]);

                Log::info('Short link created successfully.', [
                    'short_link_id' => $shortLink->id,
                    'user_id' => $user->id,
                    'slug' => $shortLink->slug,
                ]);

                return $shortLink;
            } catch (UniqueConstraintViolationException) {
                Log::warning('Short link slug collision detected during persistence.', [
                    'attempt' => $attempt,
                    'slug' => $slug,
                ]);
            }
        }

        Log::error('Short link slug generation attempts exhausted.', [
            'user_id' => $user->id,
            'retry_limit' => self::MAX_ATTEMPTS,
        ]);

        throw new RuntimeException('Unable to generate a unique short link slug.');
    }
}
