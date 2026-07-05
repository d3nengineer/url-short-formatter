<?php

namespace App\Http\Controllers;

use App\Actions\ShortLinks\ResolveShortLinkRedirectAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PublicRedirectController extends Controller
{
    public function __invoke(
        string $slug,
        Request $request,
        ResolveShortLinkRedirectAction $resolveShortLinkRedirect,
    ): RedirectResponse {
        $result = $resolveShortLinkRedirect->handle($slug, $request);

        if (! $result->available || $result->destinationUrl === null) {
            abort(404);
        }

        return redirect()->away($result->destinationUrl);
    }
}
