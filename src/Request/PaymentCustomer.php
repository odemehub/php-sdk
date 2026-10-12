<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The customer a payment is made for or a card is kept for: the whole
 * billing address and the merchant's own key for them. Nothing goes to a
 * shipping address here; the gateway turns down a payment or a kept card
 * that names one.
 *
 * The key may be left out of a payment, and the payment is then made for
 * somebody the team does not keep; but a payment that keeps its card, one
 * made with a kept card and a card kept on its own have to carry it.
 */
final readonly class PaymentCustomer
{
    public function __construct(
        /** All eight person fields; the three company fields together or not at all. */
        public Address $billingAddress,
        /** The key the merchant keeps this customer under in its own system. */
        public ?string $reference = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'reference' => $this->reference,
            'billing_address' => $this->billingAddress->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
