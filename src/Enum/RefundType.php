<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * The two ways money goes back: a cancellation takes back the whole of a
 * payment the provider has not settled yet; a refund gives back part or
 * all of one it has.
 */
enum RefundType: string
{
    case Cancel = 'cancel';
    case Refund = 'refund';
}
