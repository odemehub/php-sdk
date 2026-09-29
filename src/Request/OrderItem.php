<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One line of what an order is made up of. A line names one of the
 * merchant's products by its own key for it, and whatever it leaves unsaid
 * — the name, the price, the tax — is filled in from the product saved
 * with `saveProduct()`. What it does say holds for this order alone; the
 * product itself is never changed by an order.
 *
 * A line whose key names no product still goes through, as long as it
 * brings its own name and price: something sold once and never again does
 * not have to be saved as a product first.
 */
final readonly class OrderItem
{
    public function __construct(
        /** The key the product is saved under on the order's channel. */
        public string $channelReference,
        /** Left out, the product's own name is shown. */
        public ?string $name = null,
        /** Left out, the line is for one. */
        public ?int $quantity = null,
        /** The price of one, as digits with the kurus behind a point. Left out, the product's own price is charged. */
        public ?string $unitAmount = null,
        /** The tax included in the price, as a percentage, e.g. '20'. Left out, the product's own rate is used. */
        public ?string $taxRate = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'channel_reference' => $this->channelReference,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_amount' => $this->unitAmount,
            'tax_rate' => $this->taxRate,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
