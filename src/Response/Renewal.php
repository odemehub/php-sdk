<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * One stretch of a subscription: what it costs, when it runs and whether
 * it has been paid for. The one a subscription answers with is the
 * current one — the latest — which is unpaid until the customer has been
 * through the checkout, and dated only once it is paid.
 */
final readonly class Renewal
{
    use ReadsFields;

    public function __construct(
        /** The renewal's token in the gateway; the checkout address is built from it. */
        public string $token,
        /** What the renewal costs, with the kurus behind a point. */
        public string $amount,
        public ?Currency $currency,
        /** When the stretch began, once it has been paid for. */
        public ?string $startsAt,
        /** When the stretch runs out, which is when the next is charged. */
        public ?string $endsAt,
        /** When it was paid for, if it has been. */
        public ?string $paidAt,
    ) {}

    /**
     * Whether this renewal has been paid for.
     */
    public function isPaid(): bool
    {
        return $this->paidAt !== null;
    }

    /**
     * @param  array<string, mixed>  $renewal
     */
    public static function fromArray(array $renewal): self
    {
        return new self(
            token: self::text($renewal['token'] ?? null),
            amount: self::text($renewal['amount'] ?? null),
            currency: self::oneOf(Currency::class, $renewal['currency'] ?? null),
            startsAt: self::said($renewal['starts_at'] ?? null),
            endsAt: self::said($renewal['ends_at'] ?? null),
            paidAt: self::said($renewal['paid_at'] ?? null),
        );
    }
}
