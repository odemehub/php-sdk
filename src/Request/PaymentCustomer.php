<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The customer a payment is made for or a card is kept for: the whole
 * billing address, or what is known of it where the team fills the rest
 * from its default payer (Varsayılan Ödeyen), and the merchant's own key
 * for them. Nothing goes to a
 * shipping address here; the gateway turns down a payment or a kept card
 * that names one.
 *
 * Where the team uses a default payer, the billing address may be left out
 * altogether, and a payment may name nobody at all: a customer given
 * neither an address nor a key is not sent. Where it does not, the gateway
 * asks for the eight person fields and says so on the fields it misses.
 *
 * The key may be left out of a payment, and the payment is then made for
 * somebody the team does not keep; but a payment that keeps its card, one
 * made with a kept card and a card kept on its own have to carry it.
 */
final readonly class PaymentCustomer
{
    public function __construct(
        /** All eight person fields, unless the team fills what is left out from its default payer; the three company fields together or not at all. Left out, it is not sent. */
        public ?Address $billingAddress = null,
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
            'billing_address' => $this->billingAddress?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
