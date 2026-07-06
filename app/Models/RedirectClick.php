<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'short_link_id',
    'visitor_ip',
    'user_agent',
    'created_at',
    'updated_at',
])]
class RedirectClick extends Model
{
    /**
     * @return BelongsTo<ShortLink, $this>
     */
    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class);
    }

    /**
     * @param  Builder<RedirectClick>  $query
     * @return Builder<RedirectClick>
     */
    public function scopeForShortLink(Builder $query, ShortLink|int $shortLink): Builder
    {
        return $query->where(
            'short_link_id',
            $shortLink instanceof ShortLink ? $shortLink->id : $shortLink,
        );
    }

    /**
     * @param  Builder<RedirectClick>  $query
     * @return Builder<RedirectClick>
     */
    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->latest('created_at');
    }

    /**
     * @param  Builder<RedirectClick>  $query
     * @return Builder<RedirectClick>
     */
    public function scopeClickedBetween(
        Builder $query,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $until = null,
    ): Builder {
        return $query
            ->when($from !== null, fn (Builder $query): Builder => $query->where('created_at', '>=', $from))
            ->when($until !== null, fn (Builder $query): Builder => $query->where('created_at', '<=', $until));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
