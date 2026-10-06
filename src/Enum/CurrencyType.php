<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Whether a payment link is paid in the one money it was given (`Fixed`),
 * or the payer picks one of those it offers (`Selectable`).
 */
enum CurrencyType: string
{
    case Fixed = 'fixed';
    case Selectable = 'selectable';
}
