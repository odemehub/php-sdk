<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A card kept for a customer without a payment being made on it. The
 * provider is told who the card belongs to, so the token it hands back is
 * held under that customer and the card can be charged again later. It is
 * kept under the channel, the customer's reference and the billing e-mail
 * together; a payment with the card has to name the same three.
 *
 * Providers without a card store of their own keep a card by charging a
 * small amount and giving it straight back; those need the security code,
 * and the ones with a real card store do not. It is never stored.
 */
final readonly class CreateSavedCard extends ChannelMessage
{
    public function __construct(
        /** Who the card belongs to: the reference and the whole billing address, both required. */
        public Customer $customer,
        public Card $card,
        /** The payment account to keep the card at; it has to keep cards. Left out, the team's default account is used. */
        public ?string $paymentProviderToken = null,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'create-saved-card';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        $card = $this->card->toArray();
        unset($card['should_save']);

        return [
            'saved_card' => self::said([
                'channel_token' => $this->channel($channelToken),
                'payment_provider_token' => $this->paymentProviderToken,
            ]),
            'customer' => $this->customer->toArray(),
            'card' => $card,
        ];
    }
}
