<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * What a payment was charged when the merchant's conversion rules charged
 * it in another money than it was asked in: 100 USD asked for, 3450.00 TRY
 * taken from the card. The payment's own amount stays what was asked for;
 * this is what reached the card.
 */
final readonly class Conversion
{
    use ReadsFields;

    public function __construct(
        /** What was taken from the card. */
        public string $amount,
        /** The money it was taken in, e.g. TRY. */
        public ?Currency $currency,
        /** What a unit of the asked-for money was charged as, with any margin on top. */
        public string $rate,
    ) {}

    /**
     * @param  array<string, mixed>  $conversion
     */
    public static function fromArray(array $conversion): self
    {
        return new self(
            amount: self::text($conversion['amount'] ?? null),
            currency: self::oneOf(Currency::class, $conversion['currency'] ?? null),
            rate: self::text($conversion['rate'] ?? null),
        );
    }
}
