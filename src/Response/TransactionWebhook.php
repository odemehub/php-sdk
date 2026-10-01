<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A word the gateway sent about a payment the merchant started and the
 * customer finished — or did not — at their bank. It arrives at the
 * address the payment was started with, as plain JSON signed in the
 * `X-Signature` header, and is the same answer `retrievePayment` gives,
 * with the state reached on top: the customer may have closed the page
 * before their browser could bring the outcome back, and then this is the
 * only word the merchant hears.
 */
final readonly class TransactionWebhook extends Payment
{
    public function __construct(
        /** The state reached: successful, failed or expired. */
        public string $event,
        Result $result,
        string $transactionToken,
        string $channelToken,
        string $channelReference,
        ?string $customerChannelReference,
        ?SavedCard $savedCard = null,
        ?Conversion $conversion = null,
    ) {
        parent::__construct($result, $transactionToken, $channelToken, $channelReference, $customerChannelReference, $savedCard, $conversion);
    }

    /**
     * Whether the payment went through.
     */
    public function isSuccessful(): bool
    {
        return $this->event === 'successful';
    }

    /**
     * Whether the bank turned the payment away.
     */
    public function isFailed(): bool
    {
        return $this->event === 'failed';
    }

    /**
     * Whether the customer never opened the bank's page in time, so the
     * payment was closed without being tried.
     */
    public function isExpired(): bool
    {
        return $this->event === 'expired';
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected static function parts(array $body): array
    {
        return [
            'event' => (string) ($body['event'] ?? ''),
            ...parent::parts($body),
        ];
    }
}
