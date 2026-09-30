<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A product written down in the merchant's catalogue at the gateway, under
 * the merchant's own key for it on one of its channels. Order lines and
 * subscriptions name products by that key.
 *
 * The same key on the same channel is the same product: sending it again
 * changes the one already saved rather than saving a second, so a merchant
 * can keep its own catalogue in step by sending every change as it
 * happens. A product is never deleted; it is taken off sale by sending it
 * with `isActive: false`.
 */
final readonly class SaveProduct extends ChannelMessage
{
    public function __construct(
        /** The key the product is known by in the calling system. */
        public string $channelReference,
        public string $name,
        /** simple for something sold once, recurring for something subscribed to. */
        public string $type,
        /** The price of one, as digits with the kurus behind a point. */
        public string $amount,
        /** The tax included in the price, as a percentage, e.g. '20'. */
        public string $taxRate,
        /** How often a recurring product comes round: monthly or annually. Only a recurring product has one. */
        public ?string $period = null,
        /** Three letters, e.g. TRY. Left out, the gateway takes the lira. */
        public ?string $currency = null,
        /** Whether it is on sale. Left out, it is. */
        public ?bool $isActive = null,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'save-product';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [
            'product' => self::said([
                'channel_token' => $this->channel($channelToken),
                'channel_reference' => $this->channelReference,
                'name' => $this->name,
                'type' => $this->type,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'tax_rate' => $this->taxRate,
                'period' => $this->period,
                'is_active' => $this->isActive,
            ]),
        ];
    }
}
