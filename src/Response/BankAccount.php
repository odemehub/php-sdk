<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A bank account whose movements are read, as the gateway shows it: named
 * by its token and never with the credentials it is read with. The gateway
 * reads every account from its bank within the hour; when it last did, and
 * why it failed if it did, is said here.
 */
final readonly class BankAccount
{
    use ReadsFields;

    public function __construct(
        /** The account's token in the gateway; its movements are asked for by it. */
        public string $token,
        /** The bank's id, as `retrieveBanks()` lists it, e.g. `enpara`. */
        public string $provider,
        public string $name,
        /** The IBAN, in capitals without spaces. */
        public string $iban,
        /** When the movements were last read from the bank, ISO 8601 UTC; null until they first are. */
        public ?string $syncedAt,
        /** Why the last read from the bank failed, in the bank's words; null when it did not. */
        public ?string $lastError,
        public ?string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $bankAccount
     */
    public static function fromArray(array $bankAccount): self
    {
        return new self(
            token: self::text($bankAccount['token'] ?? null),
            provider: self::text($bankAccount['provider'] ?? null),
            name: self::text($bankAccount['name'] ?? null),
            iban: self::text($bankAccount['iban'] ?? null),
            syncedAt: self::said($bankAccount['synced_at'] ?? null),
            lastError: self::said($bankAccount['last_error'] ?? null),
            createdAt: self::said($bankAccount['created_at'] ?? null),
        );
    }
}
