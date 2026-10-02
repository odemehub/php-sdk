<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Where an order stands: what it is for, what it comes to, whether it has
 * been paid and, if so, by which payment. The one call a merchant holding
 * nothing but the order's token can make.
 */
final readonly class RetrieveOrder extends RetrieveByToken
{
    protected function endpoint(): string
    {
        return 'retrieve-order';
    }
}
