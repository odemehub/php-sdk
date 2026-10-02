<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The outcome of starting a payment the customer confirms with their bank.
 * The form the bank wants is not handed over: the gateway keeps it and
 * serves it itself, so all that comes back is the address to send the
 * customer to. Until they have been there and come back, the payment has
 * not been made. The address is good for fifteen minutes and opens once.
 */
final readonly class SecurePayment extends Payment
{
    public function __construct(
        Result $result,
        PaymentTransaction $transaction,
        ?PaymentCustomer $customer,
        /** Where the customer has to be sent. There whenever the payment started; null when the provider refused to open the 3D session. */
        public ?string $redirectUrl = null,
        ?Conversion $conversion = null,
        ?SavedCard $savedCard = null,
    ) {
        parent::__construct($result, $transaction, $customer, $conversion, $savedCard);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected static function parts(array $body): array
    {
        $result = self::object($body['result'] ?? null) ?? [];

        return [
            ...parent::parts($body),
            'redirectUrl' => self::said($result['redirect_url'] ?? null),
        ];
    }
}
