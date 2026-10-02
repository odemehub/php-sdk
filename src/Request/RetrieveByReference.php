<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One record asked after by the merchant's own reference for it on a
 * channel. The latest record under that pair is answered: the merchant
 * that opened something and lost its token, or never heard back, finds it
 * again this way. Nothing is changed by asking.
 */
abstract readonly class RetrieveByReference extends ChannelMessage
{
    public function __construct(
        /** The reference the record was made under in the calling system. */
        public string $channelReference,
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
            'channel_token' => $this->channel($channelToken),
            'channel_reference' => $this->channelReference,
        ];
    }
}
