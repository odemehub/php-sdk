<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Every subscription opened on a channel within a span of days, oldest
 * first.
 */
final readonly class RetrieveSubscriptionsByChannelReference extends RetrieveByChannelReference
{
    public function path(): string
    {
        return 'retrieve-subscriptions-by-channel-reference';
    }
}
