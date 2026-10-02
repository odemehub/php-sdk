<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The gateway answered with something that is neither an outcome nor a
 * refusal this client knows how to read: something that went wrong on the
 * gateway's own side (HTTP 500), a body that is not JSON, or a status
 * nothing here is written for.
 */
class UnexpectedResponseException extends OdemehubException
{
    public function __construct(
        string $message,
        public readonly int $status,
    ) {
        parent::__construct($message);
    }
}
