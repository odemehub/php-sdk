<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * How often a subscription's renewal comes round.
 */
enum Period: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Annually = 'annually';
}
