<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Orders asked after, each with its customer.
 */
final readonly class RetrieveOrders extends Retrieve
{
    public function path(): string
    {
        return 'retrieve-orders';
    }
}
