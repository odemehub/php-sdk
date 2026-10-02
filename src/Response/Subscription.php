<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\Period;
use Gurmehub\Odemehub\Enum\SubscriptionStatus;

/**
 * A subscription as the gateway keeps it: what is subscribed to, how it
 * may be shipped, what a renewal comes to, where it stands, the renewal it
 * is on and when the next is due. The same shape comes back whether the
 * subscription has just been opened, changed, asked after or listed, and
 * in every `subscription.*` webhook.
 *
 * The totals are priced as the lines are today; the renewal carries what
 * it was actually charged. A change to the lines re-prices every renewal
 * not yet paid and leaves the paid ones as they were.
 */
final readonly class Subscription
{
    use ReadsFields;

    /**
     * @param  list<Item>  $items  What is subscribed to.
     * @param  list<ShippingMethod>  $shippingMethods
     */
    public function __construct(
        /** The subscription's token in the gateway; name it here to ask after, change or cancel it. */
        public string $token,
        /** The channel the subscription was opened on. */
        public string $channelToken,
        /** The reference the subscription is known by in the calling system. */
        public string $channelReference,
        public ?string $description,
        /** The account it was opened with; null when none was named. */
        public ?string $paymentProviderToken,
        /** Where it stands; null for a state this client does not know. */
        public ?SubscriptionStatus $status,
        /** How often a renewal comes round; null for a period this client does not know. */
        public ?Period $period,
        /** How many renewals are paid in all; null for one that runs until cancelled. */
        public ?int $renewalLimit,
        /** How many renewals have been paid so far. */
        public int $renewalsPaid,
        public array $items,
        public array $shippingMethods,
        /** The way the payer picked; null until they have. */
        public ?ShippingMethod $shippingMethod,
        public string $subtotal,
        public string $shippingAmount,
        public string $taxAmount,
        /** What a renewal comes to in all, as the lines are priced today. */
        public string $amount,
        public ?Currency $currency,
        /** Whether it was paid for in the test environment; nothing until the first payment. */
        public ?bool $isTest,
        /** The renewal it is on: the latest one. */
        public Renewal $renewal,
        /** When the kept card is next charged; null for one that is cancelled or has not been paid yet. */
        public ?string $nextPaymentAt,
        /** When it was called off, if it has been. */
        public ?string $cancelledAt,
        public ?string $createdAt,
        /** Where the customer pays the renewal it is on, while that is still owed and the subscription is open. */
        public ?string $checkoutUrl,
        /** Who the subscription is for: the key the merchant keeps them under and their addresses. */
        public ?NamedCustomer $customer = null,
    ) {}

    /**
     * Whether the subscription is being paid for: the customer has been
     * through the checkout and the card has not since been turned away.
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    /**
     * Whether the first renewal has yet to be paid for.
     */
    public function isPending(): bool
    {
        return $this->status === SubscriptionStatus::Pending;
    }

    /**
     * Whether a renewal was left unpaid: the card was tried and turned
     * away, and the customer has been asked to pay it themselves at
     * `checkoutUrl`.
     */
    public function isPastDue(): bool
    {
        return $this->status === SubscriptionStatus::PastDue;
    }

    /**
     * Whether it is over because it was called off. One called off while a
     * paid renewal is still running stays active until that ends — read
     * `cancelledAt` for that.
     */
    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::Cancelled;
    }

    /**
     * Whether it is over because the last of its renewals has run out.
     */
    public function isCompleted(): bool
    {
        return $this->status === SubscriptionStatus::Completed;
    }

    /**
     * @param  array<string, mixed>  $subscription
     */
    public static function fromArray(array $subscription): self
    {
        $shippingMethod = self::object($subscription['shipping_method'] ?? null);
        $customer = self::object($subscription['customer'] ?? null);

        return new self(
            token: self::text($subscription['token'] ?? null),
            channelToken: self::text($subscription['channel_token'] ?? null),
            channelReference: self::text($subscription['channel_reference'] ?? null),
            description: self::said($subscription['description'] ?? null),
            paymentProviderToken: self::said($subscription['payment_provider_token'] ?? null),
            status: self::oneOf(SubscriptionStatus::class, $subscription['status'] ?? null),
            period: self::oneOf(Period::class, $subscription['period'] ?? null),
            renewalLimit: self::count($subscription['renewal_limit'] ?? null),
            renewalsPaid: (int) ($subscription['renewals_paid'] ?? 0),
            items: self::each($subscription['items'] ?? null, Item::fromArray(...)),
            shippingMethods: self::each($subscription['shipping_methods'] ?? null, ShippingMethod::fromArray(...)),
            shippingMethod: $shippingMethod === null ? null : ShippingMethod::fromArray($shippingMethod),
            subtotal: self::text($subscription['subtotal'] ?? null),
            shippingAmount: self::text($subscription['shipping_amount'] ?? null),
            taxAmount: self::text($subscription['tax_amount'] ?? null),
            amount: self::text($subscription['amount'] ?? null),
            currency: self::oneOf(Currency::class, $subscription['currency'] ?? null),
            isTest: self::flag($subscription['is_test'] ?? null),
            renewal: Renewal::fromArray(self::object($subscription['renewal'] ?? null) ?? []),
            nextPaymentAt: self::said($subscription['next_payment_at'] ?? null),
            cancelledAt: self::said($subscription['cancelled_at'] ?? null),
            createdAt: self::said($subscription['created_at'] ?? null),
            checkoutUrl: self::said($subscription['checkout_url'] ?? null),
            customer: $customer === null ? null : NamedCustomer::fromArray($customer),
        );
    }
}
