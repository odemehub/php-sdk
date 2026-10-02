<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Who an order or a subscription is for, as the gateway holds them: the
 * merchant's own key — or one of the form `guest-…` the gateway made up
 * for a payer the merchant never named — where they are billed, and where
 * the goods go when somebody said. Null as a whole while nobody has said
 * who pays.
 */
final readonly class NamedCustomer
{
    use ReadsFields;

    public function __construct(
        public string $reference,
        public Address $billingAddress,
        public ?Address $shippingAddress,
    ) {}

    /**
     * Whether the gateway made the reference up, for a payer nobody named.
     */
    public function isGuest(): bool
    {
        return str_starts_with($this->reference, 'guest-');
    }

    /**
     * @param  array<string, mixed>  $customer
     */
    public static function fromArray(array $customer): self
    {
        $shipping = self::object($customer['shipping_address'] ?? null);

        return new self(
            reference: self::text($customer['reference'] ?? null),
            billingAddress: Address::fromArray(self::object($customer['billing_address'] ?? null) ?? []),
            shippingAddress: $shipping === null ? null : Address::fromArray($shipping),
        );
    }
}
