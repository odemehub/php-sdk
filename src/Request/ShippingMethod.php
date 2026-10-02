<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One way the goods of an order or a subscription may be sent, offered to
 * the payer on the checkout page. The one they pick is added to what they
 * pay. The handle is the merchant's own key for it and has to be unique
 * within the list; the amount includes the tax, like an item's price.
 */
final readonly class ShippingMethod
{
    public function __construct(
        public string $handle,
        /** What the payer sees, e.g. 'Standart Kargo'. */
        public string $title,
        /** What it costs, tax included, as digits with the kurus behind a point; '0' for free. */
        public string $amount,
        /** The tax inside the amount, as a percentage. */
        public string $taxRate,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'amount' => $this->amount,
            'tax_rate' => $this->taxRate,
        ];
    }
}
