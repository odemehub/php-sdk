<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Where an order stands: open until it is paid, then paid.
 */
enum OrderStatus: string
{
    case Open = 'open';
    case Paid = 'paid';
}
