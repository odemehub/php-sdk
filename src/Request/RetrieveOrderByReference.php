<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The latest order opened under one of the merchant's own references on a
 * channel, with its customer.
 */
final readonly class RetrieveOrderByReference extends RetrieveByReference
{
    public function path(): string
    {
        return 'retrieve-order-by-reference';
    }
}
