<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A payment charged straight to the card, with nothing for the customer to
 * confirm. The answer is the outcome: a successful one is a settled
 * payment.
 */
final readonly class RegularPayment extends Payment
{
    public function path(): string
    {
        return 'regular-payment';
    }
}
