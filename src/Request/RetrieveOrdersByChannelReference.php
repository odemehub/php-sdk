<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Every order opened on a channel within a span of days, oldest first,
 * without their customers.
 */
final readonly class RetrieveOrdersByChannelReference extends RetrieveByChannelReference
{
    public function path(): string
    {
        return 'retrieve-orders-by-channel-reference';
    }
}
