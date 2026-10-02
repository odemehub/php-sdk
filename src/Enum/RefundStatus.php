<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Where a refund stands. A pending one already counts against what the
 * payment has left to give back.
 */
enum RefundStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
}
