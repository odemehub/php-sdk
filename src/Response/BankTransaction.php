<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A movement on a bank account, as the bank booked it. Its amount is
 * signed: money coming in is above zero, money going out below it.
 */
final readonly class BankTransaction
{
    use ReadsFields;

    public function __construct(
        /** The movement's token in the gateway. */
        public string $token,
        /** The account the movement is on. */
        public string $bankAccountToken,
        /** When the bank booked it, ISO 8601 UTC. */
        public string $bookedAt,
        /** The day the bank values it on, as `YYYY-MM-DD`, when it says. */
        public ?string $valueDate,
        /** Signed, with two decimals behind a point: '22164.96' in, '-40.00' out. */
        public string $amount,
        public ?Currency $currency,
        /** What the account held after it, when the bank says. */
        public ?string $balanceAfter,
        /** The bank's own description of it. */
        public ?string $description,
        /** What the sender wrote with the money. */
        public ?string $senderDescription,
        /** The other side's IBAN, when the bank says. */
        public ?string $counterpartyIban,
        /** The other side's tax number, when the bank says. */
        public ?string $counterpartyTaxNumber,
    ) {}

    /**
     * @param  array<string, mixed>  $bankTransaction
     */
    public static function fromArray(array $bankTransaction): self
    {
        return new self(
            token: self::text($bankTransaction['token'] ?? null),
            bankAccountToken: self::text($bankTransaction['bank_account_token'] ?? null),
            bookedAt: self::text($bankTransaction['booked_at'] ?? null),
            valueDate: self::said($bankTransaction['value_date'] ?? null),
            amount: self::text($bankTransaction['amount'] ?? null),
            currency: self::oneOf(Currency::class, $bankTransaction['currency'] ?? null),
            balanceAfter: self::said($bankTransaction['balance_after'] ?? null),
            description: self::said($bankTransaction['description'] ?? null),
            senderDescription: self::said($bankTransaction['sender_description'] ?? null),
            counterpartyIban: self::said($bankTransaction['counterparty_iban'] ?? null),
            counterpartyTaxNumber: self::said($bankTransaction['counterparty_tax_number'] ?? null),
        );
    }
}
