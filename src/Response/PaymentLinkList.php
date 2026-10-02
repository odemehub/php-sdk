<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Every payment link opened on a channel within a span of days, oldest
 * first. The days answered are the ones the gateway used: the ones asked
 * for, or the last seven when none were.
 */
final readonly class PaymentLinkList
{
    use ReadsFields;

    /**
     * @param  list<PaymentLink>  $paymentLinks
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone. */
        public string $createdFrom,
        /** The last day listed, the same way. */
        public string $createdTo,
        public array $paymentLinks,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            createdFrom: self::text($body['created_from'] ?? null),
            createdTo: self::text($body['created_to'] ?? null),
            paymentLinks: self::each($body['payment_links'] ?? null, PaymentLink::fromArray(...)),
        );
    }
}
