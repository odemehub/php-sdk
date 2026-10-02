<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The whole of a payment taken back before the provider settles it. There
 * is no amount: a cancellation is always for all of it, and a payment part
 * of which has already been refunded cannot be cancelled, only refunded
 * for the rest.
 */
final readonly class CancelPayment extends Message
{
    public function __construct(
        /** The payment's token in the gateway, as it answered when the payment was made. */
        public string $token,
    ) {}

    public function path(): string
    {
        return 'cancel-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return ['transaction' => ['token' => $this->token]];
    }
}
