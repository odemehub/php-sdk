<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One line of what a subscription is for, as the product stands today. The
 * price here is the product's current one; what the period on hand was
 * actually charged is the subscription's own amount.
 */
final readonly class SubscriptionItem
{
    public function __construct(
        /** The merchant's own key for the product. */
        public string $channelReference,
        public string $name,
        public int $quantity,
        /** The price of one, as digits with the kurus behind a point. */
        public string $unitAmount,
        /** The tax included in the price, as a percentage. */
        public ?string $taxRate,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        return new self(
            channelReference: (string) ($item['channel_reference'] ?? ''),
            name: (string) ($item['name'] ?? ''),
            quantity: (int) ($item['quantity'] ?? 0),
            unitAmount: (string) ($item['unit_amount'] ?? ''),
            taxRate: isset($item['tax_rate']) ? (string) $item['tax_rate'] : null,
        );
    }
}
