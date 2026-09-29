<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One line of what a subscription is for: one of the merchant's recurring
 * products, named by its own key for it. What it costs and how often it
 * comes round are the product's, as saved with `saveProduct()`.
 */
final readonly class SubscriptionItem
{
    public function __construct(
        /** The key the recurring product is saved under on the subscription's channel. */
        public string $channelReference,
        /** Left out, the line is for one. */
        public ?int $quantity = null,
        /**
         * The price of one for the first period only, as digits with the
         * kurus behind a point: an opening offer. The periods after it are
         * charged at the product's own price. Left out, the first period is
         * charged at that price too.
         */
        public ?string $unitAmount = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'channel_reference' => $this->channelReference,
            'quantity' => $this->quantity,
            'unit_amount' => $this->unitAmount,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
