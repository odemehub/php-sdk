<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * A subscription opened for a customer, to be paid for the first time on
 * the gateway's own page. Nothing is charged here: the answer carries the
 * address to send the customer to, and they give their card there. The
 * card is kept, because the periods to come are taken from it.
 *
 * What is subscribed to is one or more of the merchant's own recurring
 * products, named by its own key for them. They have to come round at the
 * same frequency and be priced in the same money, because a subscription
 * is charged as one thing.
 */
final readonly class SubscriptionPayment extends ChannelMessage
{
    /**
     * @param  list<SubscriptionItem>  $items  What is subscribed to; at least one line, each product once.
     */
    public function __construct(
        /** The key the subscription is known by in the calling system. */
        public string $channelReference,
        public array $items,
        /** Where the customer is posted back to, with the signed outcome, once the first period is paid. */
        public string $successUrl,
        public Customer $customer,
        /** Where the customer goes if they turn back without paying. */
        public ?string $cancelUrl = null,
        /** Where this merchant is told, signed, whenever the subscription's state changes. */
        public ?string $webhookUrl = null,
        /**
         * The payment account the subscription is paid through, by its
         * token; the card is kept there and the renewals are taken there.
         * Left out, the merchant's Gate rules pick the account, and its
         * default account is used where none of them holds.
         */
        public ?string $paymentProviderToken = null,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'subscription-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [
            'subscription' => self::said([
                'channel_token' => $this->channel($channelToken),
                'channel_reference' => $this->channelReference,
                'payment_provider_token' => $this->paymentProviderToken,
                'items' => array_map(
                    static fn (SubscriptionItem $item): array => $item->toArray(),
                    $this->items,
                ),
                'success_url' => $this->successUrl,
                'cancel_url' => $this->cancelUrl,
                'webhook_url' => $this->webhookUrl,
            ]),
            'customer' => $this->customer->toArray(),
        ];
    }
}
