<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Where an order stands: what it is for, whether it has been paid and, if
 * so, by which payment. The order is named by the token the gateway gave
 * it when it was opened, which is all a merchant holds of an order whose
 * customer never came back from the checkout. Nothing is changed by
 * asking.
 */
final readonly class RetrieveOrder extends Message
{
    public function __construct(
        /** The order's token in the gateway, as it answered when it was opened. */
        public string $orderToken,
    ) {}

    public function path(): string
    {
        return 'retrieve-order';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return ['order' => ['token' => $this->orderToken]];
    }
}
