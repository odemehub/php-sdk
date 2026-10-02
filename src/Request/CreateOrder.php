<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * An order opened to be paid once on the gateway's own checkout page. The
 * answer carries `checkout_url`; the customer is sent there, pays, and is
 * posted back to `successUrl`. The addresses set for the order's channel
 * under Webhook in the panel hear `order.paid` whether or not the customer
 * comes back.
 */
final readonly class CreateOrder extends CheckoutMessage
{
    /**
     * @param  list<Item>  $items
     * @param  list<ShippingMethod>|null  $shippingMethods
     */
    public function __construct(
        string $channelReference,
        string $successUrl,
        array $items,
        ?Customer $customer = null,
        ?string $cancelUrl = null,
        ?string $description = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?bool $requiresShippingAddress = null,
        ?array $shippingMethods = null,
        ?string $channelToken = null,
    ) {
        parent::__construct(
            channelReference: $channelReference,
            successUrl: $successUrl,
            items: $items,
            customer: $customer,
            cancelUrl: $cancelUrl,
            description: $description,
            currency: $currency,
            paymentProviderToken: $paymentProviderToken,
            requiresShippingAddress: $requiresShippingAddress,
            shippingMethods: $shippingMethods,
            channelToken: $channelToken,
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
    public function toArray(string $channelToken): array
    {
        return $this->body($this->details($this->channel($channelToken)));
    }
}
