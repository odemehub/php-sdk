<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Where a subscription stands. Only `Cancelled` may be sent by the
 * merchant, on `update-subscription`; the rest follow the payments.
 */
enum SubscriptionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
}
