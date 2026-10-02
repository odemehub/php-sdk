<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * How a payment was made: confirmed by the customer at their bank, or
 * charged straight to the card.
 */
enum SecurityType: string
{
    case Secure = 'secure';
    case Regular = 'regular';
}
