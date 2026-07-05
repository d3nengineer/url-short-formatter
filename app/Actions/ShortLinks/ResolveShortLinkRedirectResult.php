<?php

namespace App\Actions\ShortLinks;

final readonly class ResolveShortLinkRedirectResult
{
    private function __construct(
        public bool $available,
        public ?string $destinationUrl,
        public ?string $reason,
        public ?int $shortLinkId,
        public ?string $slug,
    ) {}

    public static function available(string $destinationUrl, int $shortLinkId, string $slug): self
    {
        return new self(true, $destinationUrl, null, $shortLinkId, $slug);
    }

    public static function unavailable(string $reason, ?int $shortLinkId = null, ?string $slug = null): self
    {
        return new self(false, null, $reason, $shortLinkId, $slug);
    }
}
