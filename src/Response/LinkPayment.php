<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\LinkPaymentStatus;

/**
 * One payer's payment at a payment link, as the gateway keeps it: the link
 * it was made at, what was paid and the tax in it, the coupon put on it,
 * where it stands, the payer as they billed themselves and — once it is
 * paid — the attempt that paid it, which is what is given back out of or
 * asked after with `retrievePayments`.
 *
 * It is opened by the payer paying, never by the merchant: the same link
 * is paid again and again, and each payment is one of these, numbered
 * `LINKPAY1`, `LINKPAY2` and on. The totals are what is charged, the
 * coupon already taken off.
 */
final readonly class LinkPayment
{
    use ReadsFields;

    /**
     * @param  list<Item>  $items  What was paid for: the link's lines, or the one line of the amount the payer picked.
     */
    public function __construct(
        /** The payment's token in the gateway; the same token a webhook names it by. */
        public string $token,
        /** The team's number for it: `LINKPAY{n}`. */
        public string $reference,
        /** The link it was paid at. */
        public PaymentLinkReference $paymentLink,
        /** The account it is paid through; null while none has been picked. */
        public ?string $paymentProviderToken,
        /** Where it stands; null for a state this client does not know. */
        public ?LinkPaymentStatus $status,
        public array $items,
        /** What the lines come to before tax, the coupon taken off. */
        public string $subtotal,
        /** The tax on the lines, the coupon taken off. */
        public string $taxAmount,
        /** What it comes to in all, which is what the card is charged. */
        public string $amount,
        /** The coupon the payer put on it; null when none was. */
        public ?Discount $discount,
        public ?Currency $currency,
        /** The payer as they billed themselves; null until they have. */
        public ?LinkPaymentCustomer $customer,
        /** Whether it was made in the test environment. */
        public bool $isTest,
        public ?string $createdAt,
        /** The attempt that paid it, which names it again for a refund; null while it is open. */
        public ?TransactionReference $transaction,
    ) {}

    /**
     * Whether the payment at the link has gone through.
     */
    public function isPaid(): bool
    {
        return $this->status === LinkPaymentStatus::Paid;
    }

    /**
     * @param  array<string, mixed>  $linkPayment
     */
    public static function fromArray(array $linkPayment): self
    {
        $discount = self::object($linkPayment['discount'] ?? null);
        $customer = self::object($linkPayment['customer'] ?? null);
        $transaction = self::object($linkPayment['transaction'] ?? null);

        return new self(
            token: self::text($linkPayment['token'] ?? null),
            reference: self::text($linkPayment['reference'] ?? null),
            paymentLink: PaymentLinkReference::fromArray(self::object($linkPayment['payment_link'] ?? null) ?? []),
            paymentProviderToken: self::said($linkPayment['payment_provider_token'] ?? null),
            status: self::oneOf(LinkPaymentStatus::class, $linkPayment['status'] ?? null),
            items: self::each($linkPayment['items'] ?? null, Item::fromArray(...)),
            subtotal: self::text($linkPayment['subtotal'] ?? null),
            taxAmount: self::text($linkPayment['tax_amount'] ?? null),
            amount: self::text($linkPayment['amount'] ?? null),
            discount: $discount === null ? null : Discount::fromArray($discount),
            currency: self::oneOf(Currency::class, $linkPayment['currency'] ?? null),
            customer: $customer === null ? null : LinkPaymentCustomer::fromArray($customer),
            isTest: (bool) ($linkPayment['is_test'] ?? false),
            createdAt: self::said($linkPayment['created_at'] ?? null),
            transaction: $transaction === null ? null : TransactionReference::fromArray($transaction),
        );
    }
}
