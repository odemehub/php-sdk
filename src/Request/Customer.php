<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The customer a payment is made for, an order or a subscription is opened
 * for, or a card is kept for: the merchant's own key for them, where they
 * are billed and, for goods, where those go.
 *
 * The key is what makes them one of the team's customers: the customer is
 * written under it once a payment for them goes through, and their cards
 * are kept for them and found again by it. It may be left out of a payment
 * or an order, and the payment is then made for somebody the team does not
 * keep; but a payment that keeps its card, a card kept on its own and a
 * subscription have to carry it.
 *
 * Payments and kept cards take the reference and the billing address only;
 * orders and subscriptions take any part of the three.
 */
final readonly class Customer
{
    public function __construct(
        /** The key the merchant keeps this customer under in its own system. */
        public ?string $reference = null,
        public ?Address $billingAddress = null,
        /** Orders and subscriptions only. */
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
