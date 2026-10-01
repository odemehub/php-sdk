<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * An order as the gateway keeps it: what is being paid for, what it comes
 * to, where it stands and — once it is paid — the payment that paid it.
 * The same answer comes back whether the order has just been opened, asked
 * after, or the gateway is telling the merchant it was paid.
 *
 * Nothing is charged when an order is opened: the customer has to be sent
 * to `checkoutUrl` and gives their card there. What becomes of it is
 * posted to the merchant's webhook address, if it gave one, and is always
 * there to be asked after by the order's token.
 */
final readonly class Order
{
    /**
     * @param  list<OrderItem>  $items  What the order is made up of.
     */
    public function __construct(
        public Result $result,
        /** The order's token in the gateway; name it here to ask after it later. */
        public string $token,
        /** The channel the order was opened on. */
        public string $channelToken,
        /** The number the order is known by in the calling system. */
        public string $channelReference,
        public ?string $description,
        /** Where the order stands: open until it is paid, then paid. */
        public string $status,
        public array $items,
        /** What the lines come to before tax; null when no line carried a rate. */
        public ?string $subtotal,
        /** The tax the order carries; null when no line carried a rate. */
        public ?string $taxAmount,
        /** What the order comes to, added up from its lines by the gateway. */
        public string $amount,
        public string $currency,
        /** Whether it was paid in the test environment; nothing until it is paid. */
        public ?bool $isTest,
        public ?string $createdAt,
        /** Where the customer pays, while the order is still open; null once it is paid. */
        public ?string $checkoutUrl,
        /** The token of the payment that paid the order, which names it again for a refund; null while it is open. */
        public ?string $transactionToken,
        /** The merchant's own key for the customer the order is for; null for an order opened without one. */
        public ?string $customerChannelReference,
    ) {}

    /**
     * Whether the order has been paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $order = is_array($body['order'] ?? null) ? $body['order'] : [];
        $transaction = is_array($order['transaction'] ?? null) ? $order['transaction'] : [];
        $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];

        return new self(
            result: Result::fromArray($body),
            token: (string) ($order['token'] ?? ''),
            channelToken: (string) ($order['channel_token'] ?? ''),
            channelReference: (string) ($order['channel_reference'] ?? ''),
            description: self::said($order['description'] ?? null),
            status: (string) ($order['status'] ?? ''),
            items: array_values(array_map(
                static fn (mixed $item): OrderItem => OrderItem::fromArray(is_array($item) ? $item : []),
                is_array($order['items'] ?? null) ? $order['items'] : [],
            )),
            subtotal: self::said($order['subtotal'] ?? null),
            taxAmount: self::said($order['tax_amount'] ?? null),
            amount: (string) ($order['amount'] ?? ''),
            currency: (string) ($order['currency'] ?? ''),
            isTest: isset($order['is_test']) ? (bool) $order['is_test'] : null,
            createdAt: self::said($order['created_at'] ?? null),
            checkoutUrl: self::said($order['checkout_url'] ?? null),
            transactionToken: self::said($transaction['token'] ?? null),
            customerChannelReference: self::said($customer['channel_reference'] ?? null),
        );
    }

    /**
     * A field the gateway left empty reads as nothing rather than as an
     * empty string, so there is one way of asking whether it was said.
     */
    private static function said(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
