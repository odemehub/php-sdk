<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * The money a payment is asked for in. Left out, the gateway takes the
 * lira. Instalments are only allowed when the payment is both asked for
 * and charged in lira.
 */
enum Currency: string
{
    case TRY = 'TRY';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
}
