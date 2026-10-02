<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Who a kept card belongs to: the merchant's own reference for the
 * customer. The other thing it is kept under, the channel, is on the card
 * itself.
 */
final readonly class SavedCardCustomer
{
    use ReadsFields;

    public function __construct(
        /** The key the merchant keeps the customer under. */
        public string $reference,
    ) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public static function fromArray(array $customer): self
    {
        return new self(
            reference: self::text($customer['reference'] ?? null),
        );
    }
}
