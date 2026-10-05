<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Payment links asked after, each with how many payments were made on it
 * and the latest of them, newest first.
 */
final readonly class RetrievePaymentLinks extends Retrieve
{
    public function path(): string
    {
        return 'retrieve-payment-links';
    }
}
