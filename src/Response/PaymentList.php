<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Payments asked after, each with its state, amount, customer and what
 * became of its money, the ones the bank turned away included. The answer is always a list, oldest first, and an empty one when
 * nothing matched. The days answered are the ones the gateway used, when
 * the records were asked for by the days they were made on: the ones
 * asked for, or the last seven when none were.
 */
final readonly class PaymentList
{
    use ReadsFields;

    /**
     * @param  list<Transaction>  $payments
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone; null when they were asked for by token or reference. */
        public ?string $createdFrom,
        /** The last day listed, the same way. */
        public ?string $createdTo,
        public array $payments,
    ) {}

    /**
     * The payments that went through.
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
            createdFrom: self::said($body['created_from'] ?? null),
            createdTo: self::said($body['created_to'] ?? null),
            payments: self::each($body['payments'] ?? null, Transaction::fromArray(...)),
        );
    }
}
