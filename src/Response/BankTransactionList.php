<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One page of the movements on the accounts asked after, oldest first, the
 * accounts' movements woven together by when the bank booked them; an
 * empty page when nothing matched. The days answered are the ones the
 * gateway used: the ones asked for, or the last seven when none were.
 */
final readonly class BankTransactionList
{
    use ReadsFields;

    /**
     * @param  list<BankTransaction>  $bankTransactions
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone. */
        public ?string $createdFrom,
        /** The last day listed, the same way. */
        public ?string $createdTo,
        public Pagination $pagination,
        public array $bankTransactions,
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
            pagination: Pagination::fromArray(self::object($body['pagination'] ?? null) ?? []),
            bankTransactions: self::each($body['bank_transactions'] ?? null, BankTransaction::fromArray(...)),
        );
    }
}
