<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The customer an order or a subscription is opened for: the merchant's own
 * key for them, where they are billed and, for goods, where those go. Any
 * part of the three may be given; what is left out is asked on the
 * checkout page. Payments and kept cards take a PaymentCustomer instead.
 *
 * The key is what makes them one of the team's customers: the customer is
 * written under it once a payment for them goes through, and their cards
 * are kept for them and found again by it. It may be left out of an order;
 * a subscription has to carry it.
 */
final readonly class Customer
{
    public function __construct(
        /** The key the merchant keeps this customer under in its own system. */
        public ?string $reference = null,
        public ?Address $billingAddress = null,
        public ?Address $shippingAddress = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'reference' => $this->reference,
            'billing_address' => $this->billingAddress?->toArray(),
            'shipping_address' => $this->shippingAddress?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
