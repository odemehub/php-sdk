<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to opening, changing or asking after one payment link: the
 * link as it now stands and, when asked after by its token, the latest
 * payment attempts made on it — at most fifty, newest first, the refused
 * ones included — with how many there have been in all.
 */
final readonly class PaymentLinkDetails
{
    use ReadsFields;

    /**
     * @param  list<Transaction>  $transactions  The latest attempts made on the link, at most fifty, newest first. Filled by `retrievePaymentLink` only; empty on every other answer.
     */
    public function __construct(
        public Result $result,
        public PaymentLink $paymentLink,
        public array $transactions,
        /** How many attempts have been made on the link in all, however many are listed; null on every answer but `retrievePaymentLink`. */
        public ?int $transactionsCount = null,
    ) {}

    /**
     * The attempts that went through.
     *
     * @return list<Transaction>
     */
    public function successful(): array
    {
        return array_values(array_filter(
            $this->transactions,
            static fn (Transaction $transaction): bool => $transaction->isSuccessful(),
        ));
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $link = self::object($body['payment_link'] ?? null) ?? [];

        return new self(
            result: Result::fromArray($body),
            paymentLink: PaymentLink::fromArray($link),
            transactions: self::each($link['transactions'] ?? null, Transaction::fromArray(...)),
            transactionsCount: self::count($link['transactions_count'] ?? null),
        );
    }
}
