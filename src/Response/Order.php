<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\OrderStatus;

/**
 * An order as the gateway keeps it: what is being paid for, how it may be
 * shipped, what it comes to, where it stands and — once it is paid — the
 * payment that paid it. The same shape comes back whether the order has
 * just been opened, changed, asked after or listed, and in the
 * `order.*` webhook.
 *
 * The totals are the gateway's: `subtotal` is the lines net of tax,
 * `shippingAmount` the picked method net of tax, `taxAmount` the tax on
 * both, and `amount` the whole that is charged.
 */
final readonly class Order
{
    use ReadsFields;

    /**
     * @param  list<Item>  $items  What the order is made up of.
     */
    public function __construct(
        /** The order's token in the gateway; name it here to ask after or change it later. */
        public string $token,
        /** The reference the order is known by in the calling system. */
        public string $reference,
        public ?string $description,
        /** The account the order was opened with; null when none was named, in which case it is picked at pay time. */
        public ?string $paymentProviderToken,
        /** Where the order stands: `open` until it is paid, then `paid`. */
        public ?OrderStatus $status,
        public array $items,
        /** The way the payer picked; null until they have. */
        public ?ShippingMethod $shippingMethod,
        /** What the lines come to before tax. */
        public string $subtotal,
        /** What the picked shipping method costs before tax. */
        public string $shippingAmount,
        /** The tax on the lines and the shipping together. */
        public string $taxAmount,
        /** What the order comes to in all, which is what the card is charged. */
        public string $amount,
        public ?Currency $currency,
        /** Whether it was paid in the test environment; nothing until it is paid. */
        public ?bool $isTest,
        public ?string $createdAt,
        /** Where the customer pays, while the order is still open; null once it is paid. */
        public ?string $checkoutUrl,
        /** The payment that paid the order, which names it again for a refund; null while it is open. */
        public ?TransactionReference $transaction,
        /** Who the order is for; null while nobody has said. */
        public ?NamedCustomer $customer = null,
    ) {}

    /**
     * Whether the order has been paid.
     */
    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    /**
     * @param  array<string, mixed>  $order
     */
    public static function fromArray(array $order): self
    {
        $shippingMethod = self::object($order['shipping_method'] ?? null);
        $transaction = self::object($order['transaction'] ?? null);
        $customer = self::object($order['customer'] ?? null);

        return new self(
            token: self::text($order['token'] ?? null),
            reference: self::text($order['reference'] ?? null),
            description: self::said($order['description'] ?? null),
            paymentProviderToken: self::said($order['payment_provider_token'] ?? null),
            status: self::oneOf(OrderStatus::class, $order['status'] ?? null),
            items: self::each($order['items'] ?? null, Item::fromArray(...)),
            shippingMethod: $shippingMethod === null ? null : ShippingMethod::fromArray($shippingMethod),
            subtotal: self::text($order['subtotal'] ?? null),
            shippingAmount: self::text($order['shipping_amount'] ?? null),
            taxAmount: self::text($order['tax_amount'] ?? null),
            amount: self::text($order['amount'] ?? null),
            currency: self::oneOf(Currency::class, $order['currency'] ?? null),
            isTest: self::flag($order['is_test'] ?? null),
            createdAt: self::said($order['created_at'] ?? null),
            checkoutUrl: self::said($order['checkout_url'] ?? null),
            transaction: $transaction === null ? null : TransactionReference::fromArray($transaction),
            customer: $customer === null ? null : NamedCustomer::fromArray($customer),
        );
    }
}
