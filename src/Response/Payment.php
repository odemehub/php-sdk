<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The outcome of a payment, as the gateway reports it — whether it answers
 * straight away, is asked after, or posts the outcome to a webhook once
 * the customer is home from their bank. All are the same shape, so a
 * merchant reads them the same way: how it went, which payment it was,
 * and whose.
 *
 * A payment that was turned down is an outcome like any other and arrives
 * here with `result.successful` false; only answers that were never a
 * payment outcome are raised as exceptions. A payment still under way —
 * asked after before the customer is back — is not successful yet and
 * carries no message.
 *
 * The gateway's own answers also say the payment in full under
 * `transaction`: its state, what became of its money, what was charged
 * and when. An outcome posted to a webhook only names the payment.
 */
readonly class Payment
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        /** Which payment it is — its token and reference — and, in the gateway's own answers, where it stands and what was charged. */
        public PaymentTransaction $transaction,
        /** Who it was made for, as the payment froze them. */
        public ?PaymentCustomer $customer,
        /**
         * What reached the card, for a payment the merchant's conversion
         * rules charged in another money than it was asked in; null for a
         * payment charged as it was asked.
         */
        public ?Conversion $conversion = null,
        /**
         * The card the payment kept, for a payment that asked for one to be
         * kept. It is null while nothing was kept: because the payment did
         * not go through, because the provider handed nothing back, because
         * a 3D payment has not been finished yet, or because the payment
         * never asked.
         */
        public ?SavedCard $savedCard = null,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): static
    {
        return new static(...static::parts($body));
    }

    /**
     * The pieces every payment outcome is read out of, named as the
     * constructor takes them, so a kind of payment that says more can add
     * to them rather than read the body again.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected static function parts(array $body): array
    {
        $customer = self::object($body['customer'] ?? null);
        $conversion = self::object($body['conversion'] ?? null);
        $savedCard = self::object($body['saved_card'] ?? null);

        return [
            'result' => Result::fromArray($body),
            'transaction' => PaymentTransaction::fromArray(self::object($body['transaction'] ?? null) ?? []),
            'customer' => $customer === null ? null : PaymentCustomer::fromArray($customer),
            'conversion' => $conversion === null ? null : Conversion::fromArray($conversion),
            'savedCard' => $savedCard === null ? null : SavedCard::fromArray($savedCard),
        ];
    }
}
