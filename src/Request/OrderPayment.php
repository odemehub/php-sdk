<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * An order opened to be paid on the gateway's own page. Nothing is charged
 * here: the answer carries the address to send the customer to, and they
 * give their card there. The customer is given whole or not at all: given,
 * that page asks them for nothing but the card; left out, the order is
 * opened for nobody in particular and whoever pays says who they are on
 * the page. Such a payer is never handed back — a merchant that wants to
 * know its customer keeps them on its own side and sends them.
 *
 * What the order comes to is not sent. The gateway adds up the lines and
 * answers with the amount, so the total can never disagree with what it
 * is made up of.
 */
final readonly class OrderPayment extends ChannelMessage
{
    /**
     * @param  list<OrderItem>  $items  What the order is made up of; at least one line.
     */
    public function __construct(
        /** The number the order is known by in the calling system. */
        public string $channelReference,
        /** Where the customer is posted back to, with the signed outcome, once the order is paid. */
        public string $successUrl,
        public array $items,
        /** Who the order is for; left out, the payer says on the page. */
        public ?Customer $customer = null,
        /** Where the customer goes if they turn back without paying. */
        public ?string $cancelUrl = null,
        public ?string $description = null,
        /** Three letters, e.g. TRY. Left out, the gateway takes the lira. */
        public ?string $currency = null,
        /**
         * The payment account the order is paid through, by its token.
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
        return 'order-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return self::said([
            'order' => self::said([
                'channel_token' => $this->channel($channelToken),
                'channel_reference' => $this->channelReference,
                'payment_provider_token' => $this->paymentProviderToken,
                'description' => $this->description,
                'currency' => $this->currency,
                'success_url' => $this->successUrl,
                'cancel_url' => $this->cancelUrl,
                'items' => array_map(
                    static fn (OrderItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ]),
            'customer' => $this->customer?->toArray(),
        ]);
    }
}
