<?php

namespace App\Http\Controllers;

use App\Actions\ShortLinks\CreateShortLinkAction;
use App\Actions\ShortLinks\DeleteShortLinkAction;
use App\Http\Requests\StoreShortLinkRequest;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShortLinkController extends Controller
{
    public function store(StoreShortLinkRequest $request, CreateShortLinkAction $createShortLink): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $shortLink = $createShortLink->handle($user, $request->validated('original_url'));

        return redirect()
            ->route('dashboard')
            ->with('short_link', [
                'id' => $shortLink->id,
                'slug' => $shortLink->slug,
                'url' => url($shortLink->slug),
            ]);
    }

    public function destroy(Request $request, ShortLink $shortLink, DeleteShortLinkAction $deleteShortLink): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $deleteShortLink->handle($user, $shortLink);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Short link deleted.');
    }
}
