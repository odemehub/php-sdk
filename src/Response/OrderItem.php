<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One line of what an order is made up of, as it was written down when the
 * order was opened: filled in from the catalogue where the line said
 * nothing, and as the line said where it did. A price changed since leaves
 * it as the customer was shown it.
 */
final readonly class OrderItem
{
    public function __construct(
        /** The merchant's own key for what is on the line. */
        public string $channelReference,
        public string $name,
        /** The picture the line is shown with, if any. */
        public ?string $image,
        public int $quantity,
        /** The price of one, as digits with the kurus behind a point. */
        public string $unitAmount,
        /** The tax included in the price, as a percentage; null for a line with no rate. */
        public ?string $taxRate,
        /** The tax the line comes to; null for a line with no rate. */
        public ?string $taxAmount,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        return new self(
            channelReference: (string) ($item['channel_reference'] ?? ''),
            name: (string) ($item['name'] ?? ''),
            image: isset($item['image']) ? (string) $item['image'] : null,
            quantity: (int) ($item['quantity'] ?? 0),
            unitAmount: (string) ($item['unit_amount'] ?? ''),
            taxRate: isset($item['tax_rate']) ? (string) $item['tax_rate'] : null,
            taxAmount: isset($item['tax_amount']) ? (string) $item['tax_amount'] : null,
        );
    }
}
