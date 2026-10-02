<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A message that speaks for one of the team's channels: a payment, an order
 * opened for the checkout, a card kept for a customer. The channel belongs
 * to the integration rather than to any one message, so it is named once on
 * the client; a merchant selling on more than one channel names another
 * here, on the single message that belongs elsewhere.
 */
abstract readonly class ChannelMessage extends Message
{
    /**
     * Stands for the team's own ödemehub channel, which has no token of its
     * own and is only ever reached by payment links: the panel opens its
     * links there, and a link that names no channel of the merchant's goes
     * there too. Give it as the channel of a payment link message to reach
     * those links.
     */
    public const ODEMEHUB_CHANNEL = 'odemehub';

    public function __construct(
        /** The channel this one message speaks for. Left out, the client's own is used. */
        public ?string $channelToken = null,
    ) {}

    /**
     * The channel this message is for: the one it names, or the client's.
     */
    protected function channel(string $channelToken): string
    {
        return $this->channelToken ?? $channelToken;
    }

    /**
     * The channel a payment link message is for: the one it names, the
     * client's, or — for `ODEMEHUB_CHANNEL` — none, which the gateway
     * reads as its own ödemehub channel.
     */
    protected function linkChannel(string $channelToken): ?string
    {
        return $this->channelToken === self::ODEMEHUB_CHANNEL ? null : $this->channel($channelToken);
    }
}
