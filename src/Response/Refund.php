<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\RefundType;

/**
 * What went back out of a payment: which of the two ways it went, and how
 * much. A cancellation is always for the whole payment; a refund is for
 * what was asked, or everything the payment had left when nothing was.
 */
final readonly class Refund
{
    use ReadsFields;

    public function __construct(
        /** A cancellation or a refund; null for a kind this client does not know. */
        public ?RefundType $type,
        /** How much actually went back, as digits with the kurus behind a point. */
        public string $amount,
    ) {}

    /**
     * @param  array<string, mixed>  $refund
     */
    public static function fromArray(array $refund): self
    {
        return new self(
            type: self::oneOf(RefundType::class, $refund['type'] ?? null),
            amount: self::text($refund['amount'] ?? null),
        );
    }
}
