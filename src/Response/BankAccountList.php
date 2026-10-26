<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Bank accounts asked after, oldest first. The answer is always a list,
 * and an empty one when nothing matched.
 */
final readonly class BankAccountList
{
    use ReadsFields;

    /**
     * @param  list<BankAccount>  $bankAccounts
     */
    public function __construct(
        public Result $result,
        public array $bankAccounts,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            bankAccounts: self::each($body['bank_accounts'] ?? null, BankAccount::fromArray(...)),
        );
    }
}
