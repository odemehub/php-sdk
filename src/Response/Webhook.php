<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\WebhookEvent;

/**
 * A word the gateway sent about something of the merchant's: an order
 * paid, a link paid, a subscription's state changed, a payment finished,
 * money given back. It goes to the addresses the team set for the event
 * under Webhook in the panel, as plain JSON signed the way every answer
 * is; `Client::webhook()` checks the signature before reading it.
 *
 * It is a notification, never the answer. It names the thing by token —
 * and the payment beside it when money moved — and nothing else; ask the
 * gateway what became of it (`retrieveOrders`, `retrieveLinkPayments`,
 * `retrieveSubscriptions`, `retrievePayments`, by its token) and act on
 * that, checking it against your own record. A word may arrive more than once; the id tells
 * the copies apart.
 */
final readonly class Webhook
{
    use ReadsFields;

    public function __construct(
        /** The word's own token, the same on every delivery of it. */
        public string $id,
        /** What happened; see `Enum\WebhookEvent`. */
        public string $event,
        public ?string $createdAt,
        /** The order, for the `order.*` events. */
        public ?string $orderToken,
        /** The payment link, for the `payment_link.*` events. */
        public ?string $paymentLinkToken,
        /** The subscription, for the `subscription.*` events. */
        public ?string $subscriptionToken,
        /** The payment: for the `transaction.*` events, and beside the thing wherever money moved at it. */
        public ?string $transactionToken,
        /** The payer's payment at the link, beside the link on every `payment_link.*` event. */
        public ?string $linkPaymentToken = null,
    ) {}

    /**
     * Which event this is, or null for one this client was not written for.
     */
    public function event(): ?WebhookEvent
    {
        return WebhookEvent::tryFrom($this->event);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            id: self::text($body['id'] ?? null),
            event: self::text($body['event'] ?? null),
            createdAt: self::said($body['created_at'] ?? null),
            orderToken: self::said(self::object($body['order'] ?? null)['token'] ?? null),
            paymentLinkToken: self::said(self::object($body['payment_link'] ?? null)['token'] ?? null),
            subscriptionToken: self::said(self::object($body['subscription'] ?? null)['token'] ?? null),
            transactionToken: self::said(self::object($body['transaction'] ?? null)['token'] ?? null),
            linkPaymentToken: self::said(self::object($body['link_payment'] ?? null)['token'] ?? null),
        );
    }
}
