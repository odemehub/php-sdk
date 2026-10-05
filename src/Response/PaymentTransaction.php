<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\PaymentStatus;
use Gurmehub\Odemehub\Enum\SecurityType;
use Gurmehub\Odemehub\Enum\TransactionStatus;

/**
 * The payment an outcome is about. The gateway's own answers — a payment
 * made or started, money given back, a payment asked after by token or by
 * reference — say it in full: which payment it is, where it stands, what
 * was charged and when, so a merchant checking a customer back from the
 * bank sees all of it in one go. A payment made at an order, a payment
 * link or a subscription names it, so a webhook about one of them can be
 * checked against the payment it names.
 */
final readonly class PaymentTransaction
{
    use ReadsFields;

    public function __construct(
        /** The payment's token in the gateway, which names it again to ask after or give back. */
        public string $token,
        /** The reference the payment was made under in the calling system. */
        public string $reference,
        /** The attempt's state; null for a state this client does not know. */
        public ?TransactionStatus $status = null,
        /** What became of the money; null for a state this client does not know. */
        public ?PaymentStatus $paymentStatus = null,
        /** How it was made: secure (confirmed at the bank) or regular. */
        public ?SecurityType $securityType = null,
        /** What the card was charged, with the kurus behind a point. */
        public ?string $amount = null,
        /** What was being sold, before anything added for instalments. */
        public ?string $baseAmount = null,
        public ?Currency $currency = null,
        public ?int $installmentNumber = null,
        /** Whether it was made in the test environment. */
        public ?bool $isTest = null,
        public ?string $createdAt = null,
        /** The token of the order the payment was made at, when it was made at one. */
        public ?string $orderToken = null,
        /** The token of the payment link the payment was made on, when it was made on one. */
        public ?string $paymentLinkToken = null,
        /** The token of the subscription whose renewal the payment paid, when it paid one. */
        public ?string $subscriptionToken = null,
    ) {}

    /**
     * Whether the attempt went through.
     */
    public function isSuccessful(): bool
    {
        return $this->status === TransactionStatus::Successful;
    }

    /**
     * Whether the attempt is over, one way or the other. An attempt the
     * provider never answered (`timeout`) is not, and needs looking into.
     */
    public function isFinished(): bool
    {
        return $this->status?->isFinished() ?? false;
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function fromArray(array $transaction): self
    {
        return new self(
            token: self::text($transaction['token'] ?? null),
            reference: self::text($transaction['reference'] ?? null),
            status: self::oneOf(TransactionStatus::class, $transaction['status'] ?? null),
            paymentStatus: self::oneOf(PaymentStatus::class, $transaction['payment_status'] ?? null),
            securityType: self::oneOf(SecurityType::class, $transaction['security_type'] ?? null),
            amount: self::said($transaction['amount'] ?? null),
            baseAmount: self::said($transaction['base_amount'] ?? null),
            currency: self::oneOf(Currency::class, $transaction['currency'] ?? null),
            installmentNumber: self::count($transaction['installment_number'] ?? null),
            isTest: self::flag($transaction['is_test'] ?? null),
            createdAt: self::said($transaction['created_at'] ?? null),
            orderToken: self::said(self::object($transaction['order'] ?? null)['token'] ?? null),
            paymentLinkToken: self::said(self::object($transaction['payment_link'] ?? null)['token'] ?? null),
            subscriptionToken: self::said(self::object($transaction['subscription'] ?? null)['token'] ?? null),
        );
    }
}
