<?php

namespace App\Actions\ShortLinks;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class DeleteShortLinkAction
{
    public function handle(User $user, ShortLink $shortLink): void
    {
        if ($shortLink->user_id !== $user->id) {
            Log::warning('Unauthorized short link deletion attempt denied.', [
                'user_id' => $user->id,
                'short_link_id' => $shortLink->id,
                'owner_id' => $shortLink->user_id,
            ]);

            throw new NotFoundHttpException;
        }

        try {
            $shortLink->delete();
        } catch (Throwable $exception) {
            Log::error('Short link deletion failed unexpectedly.', [
                'user_id' => $user->id,
                'short_link_id' => $shortLink->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }

        Log::info('Short link soft deleted successfully.', [
            'user_id' => $user->id,
            'short_link_id' => $shortLink->id,
            'slug' => $shortLink->slug,
        ]);
    }
}
