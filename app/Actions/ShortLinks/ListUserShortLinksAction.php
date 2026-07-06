<?php

namespace App\Actions\ShortLinks;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ListUserShortLinksAction
{
    /**
     * @return LengthAwarePaginator<int, ShortLink>
     */
    public function handle(User $user, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return ShortLink::query()
                ->whereBelongsTo($user)
                ->withCount('redirectClicks')
                ->latest('created_at')
                ->paginate($perPage)
                ->withQueryString();
        } catch (Throwable $exception) {
            Log::error('Dashboard owned short link query failed unexpectedly.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }
}
