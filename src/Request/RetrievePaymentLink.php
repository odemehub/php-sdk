<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A payment link as it stands, with the latest fifty payment attempts
 * made on it, newest first and the refused ones included, and how many
 * there have been in all. A link is paid again and again, so this is
 * where a merchant sees who paid it and when.
 */
final readonly class RetrievePaymentLink extends RetrieveByToken
{
    protected function endpoint(): string
    {
        return 'retrieve-payment-link';
    }
}
