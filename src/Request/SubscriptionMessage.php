<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something asked of a subscription that has already been opened. The
 * subscription is named by the token the gateway gave it, and nothing
 * else is sent: the gateway holds the products, the customer, the channel
 * and the periods it has been through.
 */
abstract readonly class SubscriptionMessage extends Message
{
    public function __construct(
        /** The subscription's token in the gateway, as it answered when it was opened. */
        public string $subscriptionToken,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return ['subscription' => ['token' => $this->subscriptionToken]];
    }
}
