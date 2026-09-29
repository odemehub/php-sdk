<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something done to one of a customer's kept cards. The card is named by
 * the token the gateway gave it, and the customer alongside it, so a card
 * can only ever be reached through the customer it belongs to.
 */
abstract readonly class SavedCardMessage extends ChannelMessage
{
    public function __construct(
        public NamedCustomer $customer,
        /** The card's token in the gateway, as a listing of the customer's cards gave it. */
        public string $savedCardToken,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [
            'customer' => $this->customer->toArray($this->channel($channelToken)),
            'saved_card' => ['token' => $this->savedCardToken],
        ];
    }
}
