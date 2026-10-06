<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Payments at the team's links asked after, each with the link it was paid
 * at and the attempt that paid it. The answer is always a list, oldest
 * first, and an empty one when nothing matched. The days answered are the
 * ones the gateway used, when the records were asked for by the days they
 * were made on: the ones asked for, or the last seven when none were.
 */
final readonly class LinkPaymentList
{
    use ReadsFields;

    /**
     * @param  list<LinkPayment>  $linkPayments
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone; null when they were asked for by token or reference. */
        public ?string $createdFrom,
        /** The last day listed, the same way. */
        public ?string $createdTo,
        public array $linkPayments,
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
            linkPayments: self::each($body['link_payments'] ?? null, LinkPayment::fromArray(...)),
        );
    }
}
