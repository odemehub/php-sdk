<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\PaymentStatus;

/**
 * A payment named, and nothing more: its token in the gateway,
 * it came in on, the reference it was made under and what became of its
 * money. It is how a paid order points at the payment that paid it, and
 * shows whether that money has since gone back.
 */
final readonly class TransactionReference
{
    use ReadsFields;

    public function __construct(
        /** The payment's token in the gateway, which names it again to ask after or give back. */
        public string $token,
        /** The reference the payment was made under in the calling system. */
        public string $reference,
        /** What became of the money: paid, cancelled, refunded, partially refunded; null for a state this client does not know. */
        public ?PaymentStatus $paymentStatus = null,
    ) {}

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function fromArray(array $transaction): self
    {
        return new self(
            token: self::text($transaction['token'] ?? null),
            reference: self::text($transaction['reference'] ?? null),
            paymentStatus: self::oneOf(PaymentStatus::class, $transaction['payment_status'] ?? null),
        );
    }
}
