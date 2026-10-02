<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Every record of a kind made on a channel within a span of days, oldest
 * first. The days are given as `YYYY-MM-DD` in the team's own timezone,
 * both ends included, and the span may be at most seven days; the two are
 * given together or not at all, and left out they mean the last seven days
 * up to today. Nothing is changed by asking.
 */
abstract readonly class RetrieveByChannelReference extends ChannelMessage
{
    public function __construct(
        /** The first day, as `YYYY-MM-DD`. Given together with `createdTo`. */
        public ?string $createdFrom = null,
        /** The last day, as `YYYY-MM-DD`, at most six days after the first. */
        public ?string $createdTo = null,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return self::said([
            'channel_token' => $this->channel($channelToken),
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
        ]);
    }
}
