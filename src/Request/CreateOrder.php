<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * An order to be paid once on the gateway's checkout page. The customer may
 * be left out altogether, or sent without a reference: the payer then says
 * who they are on the checkout, and is not kept as one of the team's
 * customers.
 */
final readonly class CreateOrder extends CheckoutMessage
{
    /**
     * @param  list<Item>  $items
     */
    public function __construct(
        string $reference,
        string $successUrl,
        array $items,
        ?Customer $customer = null,
        ?string $cancelUrl = null,
        ?string $description = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?bool $requiresShipping = null,
    ) {
        parent::__construct(
            reference: $reference,
            successUrl: $successUrl,
            items: $items,
            customer: $customer,
            cancelUrl: $cancelUrl,
            description: $description,
            currency: $currency,
            paymentProviderToken: $paymentProviderToken,
            requiresShipping: $requiresShipping,
        );
    }

    public function path(): string
    {
        return 'create-order';
    }

    protected function group(): string
    {
        return 'order';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->body($this->details());
    }
}
