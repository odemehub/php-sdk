<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A change to an open order, named by its token in the address and again
 * in the body. Only what is sent is written: a field left out keeps what
 * there was, lines sent replace every line there was, and customer fields
 * sent are merged over the ones the order had; a customer reference sent
 * takes the place of the one there was. A paid order, or one with a
 * payment under way, cannot be changed; the gateway says so on `token`.
 */
final readonly class UpdateOrder extends CheckoutMessage
{
    /**
     * @param  list<Item>|null  $items
     * @param  list<string>  $clear  Fields to set to nothing: 'description', 'cancel_url', 'payment_provider_token'.
     */
    public function __construct(
        /** The order's token in the gateway. */
        public string $token,
        ?string $reference = null,
        ?string $successUrl = null,
        ?array $items = null,
        ?Customer $customer = null,
        ?string $cancelUrl = null,
        ?string $description = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?bool $requiresShipping = null,
        ?bool $locksCustomer = null,
        ?bool $emailsCustomer = null,
        public array $clear = [],
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
            locksCustomer: $locksCustomer,
            emailsCustomer: $emailsCustomer,
        );
    }

    public function path(): string
    {
        return 'update-order/'.$this->token;
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
        return [
            'token' => $this->token,
            ...$this->body(self::cleared($this->details(), $this->clear)),
        ];
    }
}
