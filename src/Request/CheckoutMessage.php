<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * An order or a subscription, opened or changed, to be paid on the
 * gateway's own checkout page. Nothing is charged here: the answer carries
 * the address to send the customer to, and they give their card there.
 *
 * What it comes to is not sent. The gateway adds up the lines and the
 * shipping method the payer picks and answers with the amount, so the
 * total can never disagree with what it is made up of. The customer is
 * whatever is known: it is pre-filled on the checkout page and the payer
 * is asked for the rest.
 *
 * Opening is idempotent per channel and reference: opening again under a
 * reference that already has an open order or subscription overwrites it
 * with what is sent and answers with the one that was there, under its
 * own token. A paid order, or a subscription that has been paid, is not
 * touched, and neither is one with a payment under way — the gateway
 * says so on `channel_reference`.
 */
abstract readonly class CheckoutMessage extends ChannelMessage
{
    /**
     * @param  list<Item>|null  $items  What it is for; at least one line when opening. Sent on a change, they replace every line there was.
     * @param  list<ShippingMethod>|null  $shippingMethods  How the goods may be sent, for the payer to pick from; up to twenty. Sent on a change, they replace the ones there were, and an empty list removes them all.
     */
    public function __construct(
        /** The reference it is known by in the calling system. Has to carry at least one digit. */
        public ?string $channelReference = null,
        /** Where the customer's browser is posted back to once it is paid, with the payment's token. An https address reachable from the internet. */
        public ?string $successUrl = null,
        public ?array $items = null,
        /** Who it is for, as far as it is known. */
        public ?Customer $customer = null,
        /** Where the customer goes if they turn back without paying; shown as a link on the checkout page. */
        public ?string $cancelUrl = null,
        public ?string $description = null,
        /** Left out, the gateway takes the lira. */
        public ?Currency $currency = null,
        /**
         * The payment account it is paid through. Left out, the merchant's
         * Gate rules pick the account when the customer pays, and its
         * default account is used where none of them holds.
         */
        public ?string $paymentProviderToken = null,
        /** Whether the checkout page asks the payer where the goods go. */
        public ?bool $requiresShippingAddress = null,
        public ?array $shippingMethods = null,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    /**
     * The key the group travels under: `order` or `subscription`.
     */
    abstract protected function group(): string;

    /**
     * The group's fields, with what the caller left unsaid left out.
     *
     * @param  string|null  $channel  The channel to write; null to leave it as it is.
     * @return array<string, mixed>
     */
    protected function details(?string $channel): array
    {
        return self::said([
            'channel_token' => $channel,
            'channel_reference' => $this->channelReference,
            'description' => $this->description,
            'payment_provider_token' => $this->paymentProviderToken,
            'currency' => $this->currency?->value,
            'success_url' => $this->successUrl,
            'cancel_url' => $this->cancelUrl,
            'requires_shipping_address' => $this->requiresShippingAddress,
            'items' => $this->items === null ? null : array_map(
                static fn (Item $item): array => $item->toArray(),
                $this->items,
            ),
            'shipping_methods' => $this->shippingMethods === null ? null : array_map(
                static fn (ShippingMethod $method): array => $method->toArray(),
                $this->shippingMethods,
            ),
        ]);
    }

    /**
     * The body: the group and, beside it, the customer when one was given.
     *
     * @param  array<string, mixed>  $group  The group's fields, as `details()` builds them.
     * @return array<string, mixed>
     */
    protected function body(array $group): array
    {
        return self::said([
            $this->group() => $group,
            'customer' => $this->customer?->toArray(),
        ]);
    }

    /**
     * Fields a change sets to nothing. Leaving a field out of a change
     * keeps what there was, so clearing one — lifting a renewal limit,
     * dropping a description — has to be said on purpose.
     *
     * @param  array<string, mixed>  $group
     * @param  list<string>  $clear  Wire names: 'renewal_limit', 'description', 'cancel_url', 'payment_provider_token'.
     * @return array<string, mixed>
     */
    protected static function cleared(array $group, array $clear): array
    {
        foreach ($clear as $field) {
            $group[$field] = null;
        }

        return $group;
    }
}
