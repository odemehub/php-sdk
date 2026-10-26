<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A change to a bank account, named by its token in the address and again
 * in the body: its name, and the credentials given, the rest staying as
 * they are. New credentials are tried against the bank first and kept
 * only if it lets them read the account. Its bank and IBAN do not change.
 * A change that sends nothing but the token is turned down (422).
 */
final readonly class UpdateBankAccount extends Message
{
    /**
     * @param  array<string, string>|null  $settings  Only the credentials to replace.
     */
    public function __construct(
        /** The account's token in the gateway. */
        public string $token,
        public ?string $name = null,
        public ?array $settings = null,
    ) {}

    public function path(): string
    {
        return 'update-bank-account/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'bank_account' => self::said([
                'name' => $this->name,
                'settings' => $this->settings,
            ]),
        ];
    }
}
