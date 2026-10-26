<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Removing a bank account, named by its token in the address and again in
 * the body. Every movement read off it goes with it.
 */
final readonly class DeleteBankAccount extends Message
{
    public function __construct(
        /** The account's token in the gateway. */
        public string $token,
    ) {}

    public function path(): string
    {
        return 'delete-bank-account/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['token' => $this->token];
    }
}
