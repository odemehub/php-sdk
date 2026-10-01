<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A word the gateway sent about one of the merchant's orders. It arrives
 * at the address the order was opened with, as plain JSON signed in the
 * `X-Signature` header, and is not believed until that signature is
 * checked.
 *
 * An order is only ever told of once, when it is paid: an attempt that
 * fails leaves it open and the customer trying again on the checkout.
 */
final readonly class OrderWebhook
{
    public function __construct(
        /** The state reached: paid. */
        public string $event,
        /** The order as it stands now, with the payment that paid it. */
        public Order $order,
    ) {}

    /**
     * Whether the order has been paid, which is the one thing said here.
     */
    public function isPaid(): bool
    {
        return $this->event === 'paid';
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            event: (string) ($body['event'] ?? ''),
            order: Order::fromArray($body),
        );
    }
}
