<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A payment link asked after by the merchant's own reference for it on a
 * channel. Give `ChannelMessage::ODEMEHUB_CHANNEL` as the channel to find
 * a link on the team's own ödemehub channel, which is where the panel
 * opens its links and where a link opened without a channel went.
 */
final readonly class RetrievePaymentLinkByReference extends RetrieveByReference
{
    public function path(): string
    {
        return 'retrieve-payment-link-by-reference';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return self::said([
            'channel_token' => $this->linkChannel($channelToken),
            'channel_reference' => $this->channelReference,
        ]);
    }
}
