<?php

namespace App\Models;

use Database\Factories\ShortLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'original_url',
    'slug',
    'expires_at',
    'disabled_at',
    'last_redirected_at',
])]
class ShortLink extends Model
{
    /** @use HasFactory<ShortLinkFactory> */
    use HasFactory, SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<RedirectClick, $this>
     */
    public function redirectClicks(): HasMany
    {
        return $this->hasMany(RedirectClick::class);
    }

    /**
     * @param  Builder<ShortLink>  $query
     * @return Builder<ShortLink>
     */
    public function scopeActiveForRedirect(Builder $query): Builder
    {
        return $query
            ->whereNull('disabled_at')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function redirectUnavailableReason(): ?string
    {
        if ($this->trashed()) {
            return 'deleted';
        }

        if ($this->disabled_at !== null) {
            return 'disabled';
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return 'expired';
        }

        return null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'disabled_at' => 'datetime',
            'last_redirected_at' => 'datetime',
        ];
    }
}
