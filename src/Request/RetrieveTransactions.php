<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Every attempt at paying something the merchant names by its own number
 * on a channel: the order number it opened an order with, or started a
 * payment with. This is how a merchant sees the whole of what happened to
 * one of its orders — how many times its customer tried, which were
 * refused and which went through — rather than one payment it already
 * holds the token of. Nothing is changed by asking.
 */
final readonly class RetrieveTransactions extends ChannelMessage
{
    public function __construct(
        /** The number the payments were made under in the calling system. */
        public string $channelReference,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'retrieve-transactions';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [
            'transaction' => [
                'channel_token' => $this->channel($channelToken),
                'channel_reference' => $this->channelReference,
            ],
        ];
    }
}
