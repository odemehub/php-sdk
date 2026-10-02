<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A change to a payment link, named by its token in the address and again
 * in the body. Only what is sent is written: a field left out keeps what
 * there was, and lines sent replace every line there was. Switching off a
 * link is `isActive: false`; switching an expired one back on needs a new
 * `expiresAt` with it. A link with a payment under way cannot be changed;
 * the gateway says so on `token`.
 *
 * The channel is written only when this message names one; the client's
 * own is not sent. `ChannelMessage::ODEMEHUB_CHANNEL` moves the link to
 * the team's own ödemehub channel.
 */
final readonly class UpdatePaymentLink extends ChannelMessage
{
    /**
     * @param  list<Item>|null  $items
     * @param  list<string>  $clear  Fields to set to nothing: 'expires_at' (never runs out), 'description', 'payment_provider_token'.
     */
    public function __construct(
        /** The link's token in the gateway. */
        public string $token,
        public ?array $items = null,
        public ?Currency $currency = null,
        public ?string $channelReference = null,
        public ?string $description = null,
        public ?string $paymentProviderToken = null,
        /** As `YYYY-MM-DD` in the team's timezone; today or later. */
        public ?string $expiresAt = null,
        public ?bool $isActive = null,
        public array $clear = [],
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'update-payment-link/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        $link = self::said([
            'channel_reference' => $this->channelReference,
            'description' => $this->description,
            'payment_provider_token' => $this->paymentProviderToken,
            'currency' => $this->currency?->value,
            'expires_at' => $this->expiresAt,
            'is_active' => $this->isActive,
            'items' => $this->items === null ? null : array_map(
                static fn (Item $item): array => $item->toArray(),
                $this->items,
            ),
        ]);

        if ($this->channelToken !== null) {
            $link['channel_token'] = $this->linkChannel($channelToken);
        }

        foreach ($this->clear as $field) {
            $link[$field] = null;
        }

        return [
            'token' => $this->token,
            'payment_link' => $link,
        ];
    }
}
