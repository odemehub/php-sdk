<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The customer a payment is made for, an order is opened for or a card is
 * kept for: the merchant's own key for them, where they are billed and,
 * for goods, where those go.
 *
 * The reference is what a kept card is held under, together with the
 * channel and the billing e-mail; a payment that keeps its card, a card
 * kept on its own and a subscription all need it. A payment with a kept
 * card has to name the same reference and e-mail the card was kept with.
 * An order or a subscription opened without one is given a `guest-…`
 * reference by the gateway once somebody pays.
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
