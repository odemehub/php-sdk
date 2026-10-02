<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The gateway could not be reached at all. Whether the payment was made is
 * unknown; the payment record on the gateway says what actually happened,
 * and `retrievePaymentByReference` finds it by the merchant's own reference.
 */
class TransportException extends OdemehubException
{
    //
}
