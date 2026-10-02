<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Every payment attempt made on a channel within a span of days, oldest
 * first, so they read as the attempts were made. The days answered are
 * the ones the gateway used: the ones asked for, or the last seven when
 * none were.
 */
final readonly class PaymentList
{
    use ReadsFields;

    /**
     * @param  list<Transaction>  $payments
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone. */
        public string $createdFrom,
        /** The last day listed, the same way. */
        public string $createdTo,
        public array $payments,
    ) {}

    /**
     * The attempts that went through.
     *
     * @return list<Transaction>
     */
    public function successful(): array
    {
        return array_values(array_filter(
            $this->payments,
            static fn (Transaction $payment): bool => $payment->isSuccessful(),
        ));
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            createdFrom: self::text($body['created_from'] ?? null),
            createdTo: self::text($body['created_to'] ?? null),
            payments: self::each($body['payments'] ?? null, Transaction::fromArray(...)),
        );
    }
}
