<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\Period;

/**
 * A subscription: its first renewal paid on the gateway's checkout page,
 * and every renewal after it taken from the card kept then. It is always
 * for a customer the merchant names by reference — the card is kept for
 * that customer — and paid through an account that keeps cards.
 */
final readonly class CreateSubscription extends CheckoutMessage
{
    /**
     * @param  list<Item>  $items
     */
    public function __construct(
        string $reference,
        public Period $period,
        string $successUrl,
        array $items,
        /** Who it is for; the reference has to be there. */
        Customer $customer,
        /** How many renewals are paid in all; left out, it runs until it is called off. */
        public ?int $renewalLimit = null,
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
        return 'create-subscription';
    }

    protected function group(): string
    {
        return 'subscription';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->body(self::said([
            ...$this->details(),
            'period' => $this->period->value,
            'renewal_limit' => $this->renewalLimit,
        ]));
    }
}
