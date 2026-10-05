<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\Period;
use Gurmehub\Odemehub\Enum\SubscriptionStatus;

/**
 * A change to a subscription, named by its token in the address and again
 * in the body; calling it off is a change too, with the status `cancelled`.
 * Only what is sent is written. Once a renewal has been paid, only the
 * status, the period, the renewal limit and the prices of the same lines
 * may change; the gateway says so on `subscription`.
 */
final readonly class UpdateSubscription extends CheckoutMessage
{
    /**
     * @param  list<Item>|null  $items
     * @param  list<string>  $clear  Fields to set to nothing: 'renewal_limit', 'description', 'cancel_url', 'payment_provider_token'.
     */
    public function __construct(
        /** The subscription's token in the gateway. */
        public string $token,
        /** Only `cancelled` is taken. */
        public ?SubscriptionStatus $status = null,
        public ?Period $period = null,
        /** Never fewer than the renewals already paid. */
        public ?int $renewalLimit = null,
        ?string $reference = null,
        ?string $successUrl = null,
        ?array $items = null,
        ?Customer $customer = null,
        ?string $cancelUrl = null,
        ?string $description = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?bool $requiresShipping = null,
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
    public function toArray(): array
    {
        $group = self::said([
            ...$this->details(),
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
