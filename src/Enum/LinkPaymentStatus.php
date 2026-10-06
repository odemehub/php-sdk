<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Where a payment at a link stands: open from the moment the payer starts
 * paying, and while their bank turns them away; paid once a payment goes
 * through.
 */
enum LinkPaymentStatus: string
{
    case Open = 'open';
    case Paid = 'paid';
}
