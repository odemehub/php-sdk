<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * What became of the money a payment took, which can move on to refunded
 * long after the attempt itself is over.
 */
enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
}
