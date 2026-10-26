<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A bank account whose movements are to be read. The gateway first asks
 * the bank whether the credentials read the account with the IBAN; one
 * the bank turns down is not added, and the answer says why. An added
 * account is named by the token the gateway gives it from then on.
 */
final readonly class CreateBankAccount extends Message
{
    /**
     * @param  array<string, string>  $settings  Every credential the bank asks for, keyed the way `retrieveBanks()` lists them.
     */
    public function __construct(
        /** The bank's id, as `retrieveBanks()` lists it, e.g. `enpara`. */
        public string $provider,
        /** What the account is called in the panel and the listing. */
        public string $name,
        /** The account's IBAN; spaces are fine. */
        public string $iban,
        public array $settings,
    ) {}

    public function path(): string
    {
        return 'create-bank-account';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'bank_account' => [
                'provider' => $this->provider,
                'name' => $this->name,
                'iban' => $this->iban,
                'settings' => $this->settings,
            ],
        ];
    }
}
