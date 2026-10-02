<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The card a payment is attempted with, or kept without one. The number
 * and the security code live here and travel no further than the request
 * body: the gateway keeps only the head and the tail digits of the number
 * and no digit of the code.
 */
final readonly class Card
{
    public function __construct(
        public string $holderName,
        /** The number, 12 to 19 digits, without spaces. */
        public string $number,
        /** Two digits, e.g. 04. */
        public string $expiryMonth,
        /** Four digits, e.g. 2030. */
        public string $expiryYear,
        /**
         * Three or four digits. A payment always needs it. A card kept
         * without a payment needs it only at providers that keep a card by
         * charging and refunding a small amount; left out, it is not sent.
         */
        public ?string $securityCode = null,
        /**
         * Whether the customer asked for this card to be kept after a
         * successful payment, so they can pay with it again without typing
         * it out. Needs a customer reference to be kept under, a plan that
         * covers saved cards and an account whose provider keeps cards.
         * Read only by payments; left out, it is not sent.
         */
        public ?bool $shouldSave = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'holder_name' => $this->holderName,
            'number' => $this->number,
            'security_code' => $this->securityCode,
            'expiry_month' => $this->expiryMonth,
            'expiry_year' => $this->expiryYear,
            'should_save' => $this->shouldSave,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * Keep the number and the security code out of dumps, stack traces and
     * anything else that reads an object's properties for display. No digit
     * of either shows, not even the last four.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'holderName' => $this->holderName,
            'number' => '*****',
            'expiryMonth' => $this->expiryMonth,
            'expiryYear' => $this->expiryYear,
            'securityCode' => '*****',
            'shouldSave' => $this->shouldSave,
        ];
    }
}
