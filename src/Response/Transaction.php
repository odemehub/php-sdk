<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One attempt at a payment, as the gateway lists it: enough to tell the
 * attempts apart and see where each got to, without the answer each was
 * given at the time. Where it stands is said twice on purpose — the
 * attempt's own state, and what became of the money, which can move on
 * to refunded long after the attempt is over.
 */
final readonly class Transaction
{
    public function __construct(
        /** The payment's token in the gateway, which names it again to ask after or give back. */
        public string $token,
        /** The channel the payment came in on. */
        public string $channelToken,
        /** The reference the payment was made under in the calling system. */
        public string $channelReference,
        /** The attempt's state: started, redirected_to_secure_page, returned_from_secure_page, failed, expired or successful. */
        public string $status,
        /** What became of the money: unpaid, paid, cancelled, refunded or partially_refunded. */
        public string $paymentStatus,
        /** How it was made: secure (confirmed at the bank) or regular. */
        public string $securityType,
        /** What the card was charged, with the kurus behind a point. */
        public string $amount,
        /** What was being sold, before anything added for instalments. */
        public string $baseAmount,
        public string $currency,
        public int $installmentNumber,
        /** Whether it was made in the test environment. */
        public bool $isTest,
        /** What the provider called the refusal, for an attempt that failed. */
        public ?string $errorCode,
        /** Why it failed, written for a person. */
        public ?string $errorMessage,
        public ?string $createdAt,
        /** The merchant's own key for the customer; null for a payer the merchant never named. */
        public ?string $customerChannelReference,
        /** What reached the card when it was charged in another money; null when charged as asked. */
        public ?Conversion $conversion,
        /** The token of the order this attempt was at, when it was at one. */
        public ?string $orderToken,
        /** The token of the subscription this attempt paid a period of, when it did. */
        public ?string $subscriptionToken,
    ) {}

    /**
     * Whether the attempt went through.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function fromArray(array $transaction): self
    {
        $customer = is_array($transaction['customer'] ?? null) ? $transaction['customer'] : [];
        $conversion = $transaction['conversion'] ?? null;
        $order = is_array($transaction['order'] ?? null) ? $transaction['order'] : [];
        $subscription = is_array($transaction['subscription'] ?? null) ? $transaction['subscription'] : [];

        return new self(
            token: (string) ($transaction['token'] ?? ''),
            channelToken: (string) ($transaction['channel_token'] ?? ''),
            channelReference: (string) ($transaction['channel_reference'] ?? ''),
            status: (string) ($transaction['status'] ?? ''),
            paymentStatus: (string) ($transaction['payment_status'] ?? ''),
            securityType: (string) ($transaction['security_type'] ?? ''),
            amount: (string) ($transaction['amount'] ?? ''),
            baseAmount: (string) ($transaction['base_amount'] ?? ''),
            currency: (string) ($transaction['currency'] ?? ''),
            installmentNumber: (int) ($transaction['installment_number'] ?? 1),
            isTest: (bool) ($transaction['is_test'] ?? false),
            errorCode: self::said($transaction['error_code'] ?? null),
            errorMessage: self::said($transaction['error_message'] ?? null),
            createdAt: self::said($transaction['created_at'] ?? null),
            customerChannelReference: self::said($customer['channel_reference'] ?? null),
            conversion: is_array($conversion) ? Conversion::fromArray($conversion) : null,
            orderToken: self::said($order['token'] ?? null),
            subscriptionToken: self::said($subscription['token'] ?? null),
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
