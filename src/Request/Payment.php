<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A payment handed to the gateway. What is common to every kind of payment
 * lives here; the endpoint it is sent to is what tells the kinds apart.
 *
 * A payment is made with a card the customer typed in or with one they let
 * the merchant keep, never with both. With a kept card the payment goes
 * through the account the card is kept at, so no account is named either;
 * the gateway turns down a payment that names both.
 */
abstract readonly class Payment extends Message
{
    public function __construct(
        /**
         * The reference the payment is known by in the calling system, such
         * as SIP-10231. It has to carry at least one digit: its digits end
         * the order number the bank is sent, so the payment can be found in
         * the bank's panel by it.
         */
        public string $reference,
        /**
         * The amount, as digits with the kurus behind a point: '100', '100.1'
         * or '100.10'. A comma is refused. It is a string so that it is
         * signed and sent exactly as it is written here, with no rounding of
         * its own on the way.
         */
        public string $amount,
        /** 1 to 12. More than one only when the payment is asked for and charged in lira. */
        public int $installmentNumber,
        /** The address the customer is paying from, as the merchant sees it. */
        public string $ip,
        /**
         * Who is paying: the whole billing address and, when the merchant
         * keeps them, their reference. A payment that keeps its card, or is
         * made with a kept one, has to name the customer the card is theirs.
         */
        public Customer $customer,
        /** The card typed in. Left out only when a kept card is named instead. */
        public ?Card $card = null,
        /** A card the customer let the merchant keep, by the token the gateway gave it. */
        public ?string $savedCardToken = null,
        /** Left out, the gateway takes the lira. */
        public ?Currency $currency = null,
        /**
         * The payment account to charge through. Left out, the team's routing
         * rules pick the account, and the team's default account is used when
         * none of them holds. Never named together with a kept card.
         */
        public ?string $paymentProviderToken = null,
        /**
         * What is being sold, where the customer spreads the amount over
         * months and the bank takes something for the waiting on top of it.
         * Never more than the amount. Left out where the two are the same,
         * which is most payments.
         */
        public ?string $baseAmount = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::said([
            'transaction' => self::said([
                'reference' => $this->reference,
                'payment_provider_token' => $this->paymentProviderToken,
                'amount' => $this->amount,
                'base_amount' => $this->baseAmount,
                'currency' => $this->currency?->value,
                'installment_number' => $this->installmentNumber,
                'ip' => $this->ip,
                'saved_card_token' => $this->savedCardToken,
            ]),
            'customer' => $this->customer->toArray(),
            'card' => $this->card?->toArray(),
        ]);
    }
}
