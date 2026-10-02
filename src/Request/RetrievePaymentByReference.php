<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The latest payment made under one of the merchant's own references on a
 * channel. The merchant that sent a payment and never heard the answer —
 * the connection dropped — finds out here whether it was made, without
 * trying it again.
 */
final readonly class RetrievePaymentByReference extends RetrieveByReference
{
    public function path(): string
    {
        return 'retrieve-payment-by-reference';
    }
}
