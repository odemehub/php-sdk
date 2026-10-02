<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Letting go of a kept card, named by its token in the address and again
 * in the body. It is dropped at the provider first and with the gateway
 * after: a card at an account whose provider cannot let a card go stays,
 * and the gateway says so.
 */
final readonly class DeleteSavedCard extends Message
{
    public function __construct(
        /** The card's token in the gateway. */
        public string $token,
    ) {}

    public function path(): string
    {
        return 'delete-saved-card/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return ['token' => $this->token];
    }
}
