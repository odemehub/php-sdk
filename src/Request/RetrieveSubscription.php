<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Where a subscription stands: what it is for, the renewal it is on and
 * whether that has been paid, when the next is due, and whether it has
 * been called off. The customer is not answered here.
 */
final readonly class RetrieveSubscription extends RetrieveByToken
{
    protected function endpoint(): string
    {
        return 'retrieve-subscription';
    }
}
