<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Payment links asked after, each with how many payments were made on it
 * and the latest fifty of them. The answer is always a list, oldest first, and an empty one when
 * nothing matched. The days answered are the ones the gateway used, when
 * the records were asked for by the days they were made on: the ones
 * asked for, or the last seven when none were.
 */
final readonly class PaymentLinkList
{
    use ReadsFields;

    /**
     * @param  list<PaymentLink>  $paymentLinks
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone; null when they were asked for by token or reference. */
        public ?string $createdFrom,
        /** The last day listed, the same way. */
        public ?string $createdTo,
        public array $paymentLinks,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            createdFrom: self::said($body['created_from'] ?? null),
            createdTo: self::said($body['created_to'] ?? null),
            paymentLinks: self::each($body['payment_links'] ?? null, PaymentLink::fromArray(...)),
        );
    }
}
