<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to adding a bank account or changing one: the account as it
 * now stands. Credentials the bank turned down come back as nothing, with
 * the result saying why in the bank's words, and nothing is changed.
 */
final readonly class BankAccountDetails
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        /** The account as it now stands, or null when the bank turned the credentials down. */
        public ?BankAccount $bankAccount,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $bankAccount = self::object($body['bank_account'] ?? null);

        return new self(
            result: Result::fromArray($body),
            bankAccount: $bankAccount === null ? null : BankAccount::fromArray($bankAccount),
        );
    }
}
