<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\PaymentStatus;
use Gurmehub\Odemehub\Enum\SecurityType;
use Gurmehub\Odemehub\Enum\TransactionStatus;

/**
 * One attempt at a payment, as the gateway lists it: enough to tell the
 * attempts apart and see where each got to. Where it stands is said
 * twice on purpose — the attempt's own state, and what became of the
 * money, which can move on to refunded long after the attempt is over.
 * An attempt made on the checkout page says which order, link or
 * subscription it was for.
 */
final readonly class Transaction
{
    use ReadsFields;

    public function __construct(
        /** The payment's token in the gateway, which names it again to ask after or give back. */
        public string $token,
        /** The reference the payment was made under in the calling system. */
        public string $reference,
        /** The attempt's state; null for a state this client does not know. */
        public ?TransactionStatus $status,
        /** What became of the money; null for a state this client does not know. */
        public ?PaymentStatus $paymentStatus,
        /** How it was made: secure (confirmed at the bank) or regular. */
        public ?SecurityType $securityType,
        /** What the card was charged, with the kurus behind a point. */
        public string $amount,
        /** What was being sold, before anything added for instalments. */
        public string $baseAmount,
        public ?Currency $currency,
        public int $installmentNumber,
        /** Whether it was made in the test environment. */
        public bool $isTest,
        /** What the provider called the refusal, for an attempt that failed. */
        public ?string $errorCode,
        /** Why it failed, written for a person. */
        public ?string $errorMessage,
        public ?string $createdAt,
        /** Who it was made for, as the payment froze them. */
        public ?PaymentCustomer $customer,
        /** What reached the card when it was charged in another money; null when charged as asked. */
        public ?Conversion $conversion,
        /** The token of the order this attempt was at, when it was at one. */
        public ?string $orderToken,
        /** The token of the payment link this attempt was at, when it was at one. */
        public ?string $paymentLinkToken,
        /** The token of the subscription this attempt paid a renewal of, when it did. */
        public ?string $subscriptionToken,
        /** The card the payment kept, when it asked to keep one and went through; null otherwise. */
        public ?SavedCard $savedCard = null,
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
        $customer = self::object($transaction['customer'] ?? null);
        $conversion = self::object($transaction['conversion'] ?? null);
        $savedCard = self::object($transaction['saved_card'] ?? null);

        return new self(
            token: self::text($transaction['token'] ?? null),
            reference: self::text($transaction['reference'] ?? null),
            status: self::oneOf(TransactionStatus::class, $transaction['status'] ?? null),
            paymentStatus: self::oneOf(PaymentStatus::class, $transaction['payment_status'] ?? null),
            securityType: self::oneOf(SecurityType::class, $transaction['security_type'] ?? null),
            amount: self::text($transaction['amount'] ?? null),
            baseAmount: self::text($transaction['base_amount'] ?? null),
            currency: self::oneOf(Currency::class, $transaction['currency'] ?? null),
            installmentNumber: (int) ($transaction['installment_number'] ?? 1),
            isTest: (bool) ($transaction['is_test'] ?? false),
            errorCode: self::said($transaction['error_code'] ?? null),
            errorMessage: self::said($transaction['error_message'] ?? null),
            createdAt: self::said($transaction['created_at'] ?? null),
            customer: $customer === null ? null : PaymentCustomer::fromArray($customer),
            conversion: $conversion === null ? null : Conversion::fromArray($conversion),
            orderToken: self::said(self::object($transaction['order'] ?? null)['token'] ?? null),
            paymentLinkToken: self::said(self::object($transaction['payment_link'] ?? null)['token'] ?? null),
            subscriptionToken: self::said(self::object($transaction['subscription'] ?? null)['token'] ?? null),
            savedCard: $savedCard === null ? null : SavedCard::fromArray($savedCard),
        );
    }
}
