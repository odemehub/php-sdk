<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Money given back out of a payment the provider has already settled, whole
 * or in part. The payment is named by the token the gateway gave it, and
 * nothing else is sent: the gateway holds the account, the provider and the
 * reference the provider knows the payment by.
 */
final readonly class RefundPayment extends Message
{
    public function __construct(
        /** The payment's token in the gateway, as it answered when the payment was made. */
        public string $token,
        /**
         * How much goes back, as digits with the kurus behind a point:
         * '35.50'. Leave it out and everything the payment has left in it
         * goes back, which is the whole of it until part of it has already
         * been given back. It is never more than the payment has left: the
         * gateway turns down anything larger.
         */
        public ?string $amount = null,
    ) {}

    public function path(): string
    {
        return 'refund-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return self::said([
            'transaction' => ['token' => $this->token],
            'amount' => $this->amount,
        ]);
    }
}
