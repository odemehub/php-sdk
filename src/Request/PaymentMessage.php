<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something asked of a payment that has already been made. The payment is
 * named by the token the gateway gave it, and nothing else is sent: the
 * gateway holds the account, the provider, the channel and the reference
 * the provider knows the payment by.
 */
abstract readonly class PaymentMessage extends Message
{
    public function __construct(
        /** The payment's token in the gateway, as it answered when the payment was made. */
        public string $transactionToken,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return ['transaction' => ['token' => $this->transactionToken]];
    }
}
