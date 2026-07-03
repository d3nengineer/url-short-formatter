<?php

namespace App\Models;

use Database\Factories\ShortLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
