<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The movements on one or more bank accounts, named by their tokens,
 * booked between two days as `YYYY-MM-DD` in the team's own timezone, at
 * most seven days apart, the last seven when none are given. The days are
 * the bank's: the day it booked the movement. The answer comes a page at
 * a time, oldest first, the accounts' movements woven together.
 */
final readonly class RetrieveBankTransactions extends Message
{
    /**
     * @param  list<string>  $bankAccountTokens  At least one account's token.
     */
    public function __construct(
        public array $bankAccountTokens,
        /** The first day, as `YYYY-MM-DD`. Given together with `createdTo`. */
        public ?string $createdFrom = null,
        /** The last day, as `YYYY-MM-DD`, at most six days after the first. */
        public ?string $createdTo = null,
        /** The page, from 1. */
        public ?int $page = null,
        /** How many movements a page holds, at most 250; 50 when a page is given without it, 250 when neither is. */
        public ?int $perPage = null,
    ) {}

    public function path(): string
    {
        return 'retrieve-bank-transactions';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::said([
            'bank_account_tokens' => array_values($this->bankAccountTokens),
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ]);
    }
}
