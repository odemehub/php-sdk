<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One way the goods may be sent, as offered on the checkout page, or the
 * one the payer picked. The amount includes the tax.
 */
final readonly class ShippingMethod
{
    use ReadsFields;

    public function __construct(
        /** The merchant's own key for it. */
        public string $handle,
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
            handle: self::text($method['handle'] ?? null),
            title: self::text($method['title'] ?? null),
            amount: self::text($method['amount'] ?? null),
            taxRate: self::text($method['tax_rate'] ?? null),
        );
    }
}
