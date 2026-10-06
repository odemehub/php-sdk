<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The coupon the payer put on something at the checkout page: the code
 * they typed and what it took off the lines, in the thing's own money.
 * Coupons are only ever typed in on the checkout page; nothing about them
 * is sent through the gateway.
 */
final readonly class Discount
{
    use ReadsFields;

    public function __construct(
        /** The code the payer typed. */
        public string $code,
        /** What it took off the lines, with the kurus behind a point. */
        public string $amount,
    ) {}

    /**
     * @param  array<string, mixed>  $discount
     */
    public static function fromArray(array $discount): self
    {
        return new self(
            code: self::text($discount['code'] ?? null),
            amount: self::text($discount['amount'] ?? null),
        );
    }
}
