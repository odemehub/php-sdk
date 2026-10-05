<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A card kept for a customer without a payment being made on it. The
 * customer is given by reference with the whole billing address: once the
 * provider takes the card, the team's customer under that reference is
 * written from what was sent and the card is kept for them.
 */
final readonly class CreateSavedCard extends Message
{
    public function __construct(
        /** Who the card is kept for: the reference and the whole billing address. */
        public Customer $customer,
        /** The card. Its security code is only needed by the providers that keep a card on the back of a small charge they take back. */
        public Card $card,
        /** The account the card is kept at; left out, the team's default account. It has to keep cards. */
        public ?string $paymentProviderToken = null,
    ) {}

    public function path(): string
    {
        return 'create-saved-card';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $card = $this->card->toArray();
        unset($card['should_save']);

        return self::said([
            'saved_card' => $this->paymentProviderToken === null ? null : ['payment_provider_token' => $this->paymentProviderToken],
            'customer' => $this->customer->toArray(),
            'card' => $card,
        ]);
    }
}
