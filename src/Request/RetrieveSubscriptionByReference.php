<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The latest subscription opened under one of the merchant's own
 * references on a channel.
 */
final readonly class RetrieveSubscriptionByReference extends RetrieveByReference
{
    public function path(): string
    {
        return 'retrieve-subscription-by-reference';
    }
}
