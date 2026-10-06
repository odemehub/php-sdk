<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Payments made at the team's payment links asked after: one by its token,
 * one by its number (`LINKPAY1`, `LINKPAY2`…), or the ones made between two
 * days. A payment at a link is opened by the payer paying, never by the
 * merchant, so it is only ever asked after.
 */
final readonly class RetrieveLinkPayments extends Retrieve
{
    public function path(): string
    {
        return 'retrieve-link-payments';
    }
}
