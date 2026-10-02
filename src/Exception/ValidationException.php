<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Exception;

/**
 * The request reached the gateway and was signed correctly, but its contents
 * were refused (HTTP 422). Nothing was done. The errors are keyed by the
 * field's dotted path in the body — `transaction.amount`,
 * `order.items.0.name`, `customer.billing_address.email`, `token` — each
 * with the reasons it was refused, in Turkish.
 */
class ValidationException extends OdemehubException
{
    /**
     * @param  array<string, list<string>>  $errors  The refused fields, each with the reasons it was refused.
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
