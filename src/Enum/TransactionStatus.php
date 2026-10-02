<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Enum;

/**
 * Where a payment attempt stands. The first three are on the way; the
 * last three are finished. `Timeout` is neither: the provider never
 * answered, and what became of the money has to be looked into by hand.
 */
enum TransactionStatus: string
{
    case Started = 'started';
    case RedirectedToSecurePage = 'redirected_to_secure_page';
    case ReturnedFromSecurePage = 'returned_from_secure_page';
    case Timeout = 'timeout';
    case Failed = 'failed';
    case Expired = 'expired';
    case Successful = 'successful';

    /**
     * Whether the attempt is over, one way or the other.
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::Successful, self::Failed, self::Expired], true);
    }
}
