<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Who a payment was made for, as the payment froze it: the merchant's own
 * key for them, if the payment named one, and the billing address as it
 * was at the time.
 */
final readonly class PaymentCustomer
{
    use ReadsFields;

    public function __construct(
        /** The merchant's own key; null for a payer the merchant never named. */
        public ?string $reference,
        public Address $billingAddress,
    ) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public static function fromArray(array $customer): self
    {
        return new self(
            reference: self::said($customer['reference'] ?? null),
            billingAddress: Address::fromArray(self::object($customer['billing_address'] ?? null) ?? []),
        );
    }
}
