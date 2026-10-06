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
 * subscription has just been opened, changed, asked after or listed; a
 * `subscription.*` webhook only names it by token.
 *
 * The totals are priced as the lines are today; the renewal carries what
 * it was actually charged. A change to the lines re-prices every renewal
 * not yet paid and leaves the paid ones as they were. A coupon is only
 * ever put on the first payment: `discount` names it, the totals here
 * stay as the lines are, and what the first renewal was charged is on
 * `renewal->amount`.
 */
final readonly class Subscription
{
    use ReadsFields;

    /**
     * @param  list<Item>  $items  What is subscribed to.
     */
    public function __construct(
        /** The subscription's token in the gateway; name it here to ask after, change or cancel it. */
        public string $token,
        /** The reference the subscription is known by in the calling system. */
        public string $reference,
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
        /** The coupon the payer put on the first payment; null when none was. */
        public ?Discount $discount = null,
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
        $discount = self::object($subscription['discount'] ?? null);

        return new self(
            token: self::text($subscription['token'] ?? null),
            reference: self::text($subscription['reference'] ?? null),
            description: self::said($subscription['description'] ?? null),
            paymentProviderToken: self::said($subscription['payment_provider_token'] ?? null),
            status: self::oneOf(SubscriptionStatus::class, $subscription['status'] ?? null),
            period: self::oneOf(Period::class, $subscription['period'] ?? null),
            renewalLimit: self::count($subscription['renewal_limit'] ?? null),
            renewalsPaid: (int) ($subscription['renewals_paid'] ?? 0),
            items: self::each($subscription['items'] ?? null, Item::fromArray(...)),
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
            discount: $discount === null ? null : Discount::fromArray($discount),
        );
    }
}
