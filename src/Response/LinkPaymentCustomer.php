<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Who paid at a link, as they billed themselves on the checkout page. A
 * payer at a link is never kept as one of the team's customers, so there
 * is no reference: only the billing address they gave.
 */
final readonly class LinkPaymentCustomer
{
    use ReadsFields;

    public function __construct(
        public Address $billingAddress,
    ) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public static function fromArray(array $customer): self
    {
        return new self(
            billingAddress: Address::fromArray(self::object($customer['billing_address'] ?? null) ?? []),
        );
    }
}
