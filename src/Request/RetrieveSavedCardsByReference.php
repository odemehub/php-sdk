<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The cards kept for a customer, named by the two things a card is kept
 * under: the channel and the merchant's reference for them. The card they
 * pay with unless they say otherwise comes first, the rest oldest first; a
 * customer with none answers an empty list.
 */
final readonly class RetrieveSavedCardsByReference extends ChannelMessage
{
    public function __construct(
        /** The key the merchant keeps the customer under. */
        public string $customerReference,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'retrieve-saved-cards-by-reference';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [
            'channel_token' => $this->channel($channelToken),
            'customer_reference' => $this->customerReference,
        ];
    }
}
