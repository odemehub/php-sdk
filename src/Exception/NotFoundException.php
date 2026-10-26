<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * What the request named is not there (HTTP 404): no payment, order,
 * payment link, subscription, kept card or bank account of the team's
 * under the token a change, a removal, a refund or a cancellation names,
 * or no team at that address. Nothing was done. The message says what
 * was looked for. Asking after records never raises this: a token,
 * reference or stretch of days that finds nothing comes back as an empty
 * list.
 */
class NotFoundException extends OdemehubException
{
    //
}
