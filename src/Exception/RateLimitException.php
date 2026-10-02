<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The team sent more requests in a minute than the gateway takes (HTTP 429):
 * 300 across every endpoint, 60 for the ones that move money or keep a
 * card. Nothing was done; the same request can be sent again once the
 * minute is over.
 */
class RateLimitException extends OdemehubException
{
    public function __construct(
        string $message,
        /** How many seconds to wait before trying again, as the gateway said it in `Retry-After`; null when it did not say. */
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }
}
