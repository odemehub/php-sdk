<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Every attempt made under one of the merchant's own numbers on a channel,
 * oldest first, so they read as the attempts were made. A merchant that
 * opened an order and heard nothing back sees here how many times its
 * customer tried and how each try went.
 */
final readonly class Transactions
{
    /**
     * @param  list<Transaction>  $transactions
     */
    public function __construct(
        public Result $result,
        public array $transactions,
    ) {}

    /**
     * The attempt that went through, if one did.
     */
    public function successful(): ?Transaction
    {
        foreach ($this->transactions as $transaction) {
            if ($transaction->isSuccessful()) {
                return $transaction;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            transactions: array_values(array_map(
                static fn (mixed $transaction): Transaction => Transaction::fromArray(is_array($transaction) ? $transaction : []),
                is_array($body['transactions'] ?? null) ? $body['transactions'] : [],
            )),
        );
    }
}
