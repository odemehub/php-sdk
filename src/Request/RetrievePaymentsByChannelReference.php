<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Every payment attempt made on a channel within a span of days, the
 * refused and the expired ones included, and the ones made on the
 * gateway's own checkout page too. This is where a payment's state,
 * amount and what became of its money are read; the outcome a single
 * payment answers with carries none of those.
 */
final readonly class RetrievePaymentsByChannelReference extends RetrieveByChannelReference
{
    public function path(): string
    {
        return 'retrieve-payments-by-channel-reference';
    }
}
