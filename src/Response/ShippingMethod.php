<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The way the payer picked to have the goods sent, from the team's own
 * list, as it was copied onto the order or the subscription. The amount
 * includes the tax.
 */
final readonly class ShippingMethod
{
    use ReadsFields;

    public function __construct(
        /** The merchant's own key for it. */
        public string $reference,
        public string $title,
        /** What it costs, tax included. */
        public string $amount,
        /** The tax inside the amount, as a percentage. */
        public string $taxRate,
    ) {}

    /**
     * @param  array<string, mixed>  $method
     */
    public static function fromArray(array $method): self
    {
        return new self(
            reference: self::text($method['reference'] ?? null),
            title: self::text($method['title'] ?? null),
            amount: self::text($method['amount'] ?? null),
            taxRate: self::text($method['tax_rate'] ?? null),
        );
    }
}
