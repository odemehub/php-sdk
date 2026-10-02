<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Every payment link opened on a channel within a span of days, oldest
 * first. Give `ChannelMessage::ODEMEHUB_CHANNEL` as the channel to list
 * the links on the team's own ödemehub channel.
 */
final readonly class RetrievePaymentLinksByChannelReference extends RetrieveByChannelReference
{
    public function path(): string
    {
        return 'retrieve-payment-links-by-channel-reference';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return self::said([
            'channel_token' => $this->linkChannel($channelToken),
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
        ]);
    }
}
