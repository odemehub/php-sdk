<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\Period;

/**
 * A subscription opened for a customer, its first renewal to be paid on
 * the gateway's own checkout page and the rest taken from the card kept
 * then. The answer carries `checkout_url`; the customer is sent there,
 * pays with a card the gateway keeps as their default, and is posted back
 * to `successUrl`. The addresses set for its channel under Webhook in the
 * panel hear every change of state after that: each renewal paid, one that
 * could not be, a cancellation, the end.
 *
 * The customer needs a reference, because that is what the card the
 * renewals are taken from is kept under. The account, named or default,
 * has to keep cards and take 3D payments, and the plan has to cover saved
 * cards.
 */
final readonly class CreateSubscription extends CheckoutMessage
{
    /**
     * @param  list<Item>  $items
     * @param  list<ShippingMethod>|null  $shippingMethods
     */
    public function __construct(
        string $channelReference,
        /** How often a renewal comes round. */
        public Period $period,
        string $successUrl,
        array $items,
        /** Who is subscribing; the reference is required. */
        Customer $customer,
        /** How many renewals are paid in all, 1 to 1000, after which it is completed. Left out, it runs until cancelled. */
        public ?int $renewalLimit = null,
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
        return 'create-subscription';
    }

    protected function group(): string
    {
        return 'subscription';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return $this->body(self::said([
            ...$this->details($this->channel($channelToken)),
            'period' => $this->period->value,
            'renewal_limit' => $this->renewalLimit,
        ]));
    }
}
