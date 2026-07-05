<?php

namespace App\Http\Controllers;

use App\Actions\ShortLinks\CreateShortLinkAction;
use App\Http\Requests\StoreShortLinkRequest;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ShortLinkController extends Controller
{
    public function store(StoreShortLinkRequest $request, CreateShortLinkAction $createShortLink): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $shortLink = $createShortLink->handle($user, $request->validated('original_url'));
        } catch (Throwable $exception) {
            Log::error('Short link creation request failed unexpectedly.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }

        Log::info('Short link creation request completed successfully.', [
            'short_link_id' => $shortLink->id,
            'user_id' => $user->id,
            'slug' => $shortLink->slug,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('short_link', [
                'id' => $shortLink->id,
                'slug' => $shortLink->slug,
                'url' => $this->shortUrlFor($shortLink),
            ]);
    }

    private function shortUrlFor(ShortLink $shortLink): string
    {
        return url($shortLink->slug);
    }
}
