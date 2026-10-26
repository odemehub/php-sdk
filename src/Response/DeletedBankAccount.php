<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to removing a bank account. There is nothing left of the
 * account to show, so it comes back carrying only the token it had.
 */
final readonly class DeletedBankAccount
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        /** The token the account had. */
        public string $bankAccountToken,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $bankAccount = self::object($body['bank_account'] ?? null) ?? [];

        return new self(
            result: Result::fromArray($body),
            bankAccountToken: self::text($bankAccount['token'] ?? null),
        );
    }
}
