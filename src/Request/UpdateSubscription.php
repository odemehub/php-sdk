<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\Period;
use Gurmehub\Odemehub\Enum\SubscriptionStatus;

/**
 * A change to a subscription, named by its token in the address and again
 * in the body. Only what is sent is written: lines sent replace the lines
 * there were and re-price every renewal not yet paid, a new period reaches
 * the next renewal, a renewal limit may not fall below what has already
 * been paid, and customer fields sent are merged over the ones there were.
 *
 * This is also how a subscription is called off: send the status
 * `cancelled`, the one status a merchant may set. Nothing is charged after
 * that and nothing is given back; a renewal already paid is served to its
 * end, and the subscription ends then. A subscription that is over, or
 * has a payment under way, cannot be changed; the gateway says so on
 * `token`.
 *
 * The channel is written only when this message names one; the client's
 * own is not sent.
 */
final readonly class UpdateSubscription extends CheckoutMessage
{
    /**
     * @param  list<Item>|null  $items
     * @param  list<ShippingMethod>|null  $shippingMethods
     * @param  list<string>  $clear  Fields to set to nothing: 'renewal_limit' (run until cancelled), 'description', 'cancel_url', 'payment_provider_token'.
     */
    public function __construct(
        /** The subscription's token in the gateway. */
        public string $token,
        /** Only `SubscriptionStatus::Cancelled` is accepted; the other states follow the payments. */
        public ?SubscriptionStatus $status = null,
        public ?Period $period = null,
        /** 1 to 1000, and never fewer than the renewals already paid. */
        public ?int $renewalLimit = null,
        ?string $channelReference = null,
        ?string $successUrl = null,
        ?array $items = null,
        ?Customer $customer = null,
        ?string $cancelUrl = null,
        ?string $description = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?bool $requiresShippingAddress = null,
        ?array $shippingMethods = null,
        public array $clear = [],
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
        return 'update-subscription/'.$this->token;
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
        $group = self::said([
            ...$this->details($this->channelToken),
            'period' => $this->period?->value,
            'renewal_limit' => $this->renewalLimit,
            'status' => $this->status?->value,
        ]);

        return [
            'token' => $this->token,
            ...$this->body(self::cleared($group, $this->clear)),
        ];
    }
}
