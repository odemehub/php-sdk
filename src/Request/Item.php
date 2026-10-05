<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One line of what an order, a subscription or a payment link is for. The
 * unit price includes the tax: a line of 120 at 20% is 100 of goods and 20
 * of tax, and the gateway splits it so. What the whole comes to is never
 * sent; the gateway adds the lines up and answers with the total.
 */
final readonly class Item
{
    public function __construct(
        public string $name,
        /** The price of one, tax included, as digits with the kurus behind a point: '120.00'. */
        public string $unitAmount,
        /** 1 to 9999. */
        public int $quantity,
        /** The tax inside the price, as a percentage: '20' or '20.00'. Left out, the line carries no tax. */
        public ?string $taxRate = null,
        /** The merchant's own key for what is on the line, if it has one. */
        public ?string $reference = null,
        /** The https address of the picture shown beside the line at checkout. */
        public ?string $image = null,
        /**
         * Whether the line is also kept on the team's product list: written
         * there under its reference, or the product with that reference
         * brought up to the line. A line kept so has to carry a reference.
         */
        public ?bool $saveAsProduct = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'reference' => $this->reference,
            'name' => $this->name,
            'image' => $this->image,
            'quantity' => $this->quantity,
            'unit_amount' => $this->unitAmount,
            'tax_rate' => $this->taxRate,
            'save_as_product' => $this->saveAsProduct,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
