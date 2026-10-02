<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A payment the customer confirms with their bank. The gateway does not
 * settle it; it hands back the address the customer has to be sent to, and
 * posts them back to the address named here once they are done.
 */
final readonly class SecurePayment extends Payment
{
    public function __construct(
        string $channelReference,
        string $amount,
        int $installmentNumber,
        string $ip,
        Customer $customer,
        /**
         * Where the customer's browser is posted back to once they are done
         * at their bank, with the payment's token, the reference and a hint
         * at how it went. An https address reachable from the internet.
         */
        public string $callbackUrl,
        ?Card $card = null,
        ?string $savedCardToken = null,
        ?Currency $currency = null,
        ?string $paymentProviderToken = null,
        ?string $baseAmount = null,
        ?string $channelToken = null,
    ) {
        parent::__construct(
            channelReference: $channelReference,
            amount: $amount,
            installmentNumber: $installmentNumber,
            ip: $ip,
            customer: $customer,
            card: $card,
            savedCardToken: $savedCardToken,
            currency: $currency,
            paymentProviderToken: $paymentProviderToken,
            baseAmount: $baseAmount,
            channelToken: $channelToken,
        );
    }

    public function path(): string
    {
        return 'secure-payment';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        $body = parent::toArray($channelToken);
        $body['transaction'] = self::said([
            ...$body['transaction'],
            'callback_url' => $this->callbackUrl,
        ]);

        return $body;
    }
}
