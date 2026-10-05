<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A change to a kept card, named by its token in the address and again in
 * the body. The one thing that may be changed is whether it is the
 * customer's default: a card is made the default here, and stops being
 * one when another card of the customer's is made the default instead.
 */
final readonly class UpdateSavedCard extends Message
{
    public function __construct(
        /** The card's token in the gateway. */
        public string $token,
        /** Has to be true; the gateway turns down anything else. */
        public bool $isDefault = true,
    ) {}

    public function path(): string
    {
        return 'update-saved-card/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'saved_card' => ['is_default' => $this->isDefault],
        ];
    }
}
