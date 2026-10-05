<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Who an order or a subscription is for, as the gateway holds them: the
 * merchant's own key for them, where they are billed, and where the goods
 * go when somebody said. Null as a whole while nothing at all has been
 * said of who it is for. The key is null for an order opened for somebody
 * the team does not keep; the addresses are null until somebody gives
 * them.
 */
final readonly class NamedCustomer
{
    use ReadsFields;

    public function __construct(
        public ?string $reference,
        public ?Address $billingAddress,
        public ?Address $shippingAddress,
    ) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public static function fromArray(array $customer): self
    {
        $billing = self::object($customer['billing_address'] ?? null);
        $shipping = self::object($customer['shipping_address'] ?? null);

        return new self(
            reference: self::said($customer['reference'] ?? null),
            billingAddress: $billing === null ? null : Address::fromArray($billing),
            shippingAddress: $shipping === null ? null : Address::fromArray($shipping),
        );
    }
}
