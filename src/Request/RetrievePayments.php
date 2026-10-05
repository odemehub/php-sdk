<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Payments asked after: one by its token, every attempt made under the
 * merchant's reference, or the ones made between two days — the ones the
 * bank turned away included.
 */
final readonly class RetrievePayments extends Retrieve
{
    public function path(): string
    {
        return 'retrieve-payments';
    }
}
