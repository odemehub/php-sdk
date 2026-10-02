<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One line of what an order, a subscription or a payment link is for, as
 * the gateway holds it. The unit price includes the tax, as it was sent.
 */
final readonly class Item
{
    use ReadsFields;

    public function __construct(
        /** The merchant's own key for what is on the line, if it gave one. */
        public ?string $channelReference,
        public string $name,
        /** The picture the line is shown with, if any. */
        public ?string $image,
        public int $quantity,
        /** The price of one, tax included, as digits with the kurus behind a point. */
        public string $unitAmount,
        /** The tax inside the price, as a percentage. */
        public string $taxRate,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        return new self(
            channelReference: self::said($item['channel_reference'] ?? null),
            name: self::text($item['name'] ?? null),
            image: self::said($item['image'] ?? null),
            quantity: (int) ($item['quantity'] ?? 0),
            unitAmount: self::text($item['unit_amount'] ?? null),
            taxRate: self::text($item['tax_rate'] ?? null),
        );
    }
}
