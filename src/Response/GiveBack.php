<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * What became of money asked back out of a payment. An attempt the provider
 * turned down is an outcome like any other and arrives here with
 * `result.successful` false; only answers that were never an outcome at
 * all are raised as exceptions.
 *
 * A cancellation and a refund answer the same way, so both come back as
 * this: the payment outcome and, under `refund`, which of the two it was
 * and how much went back.
 */
final readonly class GiveBack extends Payment
{
    public function __construct(
        Result $result,
        PaymentTransaction $transaction,
        ?PaymentCustomer $customer,
        /** What went back; null when the gateway answered without it. */
        public ?Refund $refund = null,
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
        $refund = self::object($body['refund'] ?? null);

        return [
            ...parent::parts($body),
            'refund' => $refund === null ? null : Refund::fromArray($refund),
        ];
    }
}
