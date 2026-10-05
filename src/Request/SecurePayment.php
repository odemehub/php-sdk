<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A payment the customer confirms with their bank. The answer carries the
 * address to send the customer to; the outcome reaches the callback address
 * once they come back.
 */
final readonly class SecurePayment extends Payment
{
    public function __construct(
        string $reference,
        string $amount,
        int $installmentNumber,
        string $ip,
        Customer $customer,
        /** Where the customer's browser is posted back to once the bank has answered. An https address reachable from the internet. */
        public string $callbackUrl,
        ?Card $card = null,
        ?string $savedCardToken = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?string $baseAmount = null,
    ) {
        parent::__construct(
            reference: $reference,
            amount: $amount,
            installmentNumber: $installmentNumber,
            ip: $ip,
            customer: $customer,
            card: $card,
            savedCardToken: $savedCardToken,
            currency: $currency,
            paymentProviderToken: $paymentProviderToken,
            baseAmount: $baseAmount,
        );
    }

    public function path(): string
    {
        return 'secure-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $body = parent::toArray();
        $body['transaction'] = self::said([
            ...$body['transaction'],
            'callback_url' => $this->callbackUrl,
        ]);

        return $body;
    }
}
