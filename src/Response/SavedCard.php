<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A card a customer let the merchant keep, as the gateway shows it: enough
 * to draw the card and to name it again, and nothing that could charge it.
 * What lets it be charged again stays with the gateway.
 */
final readonly class SavedCard
{
    public function __construct(
        /** The card's token in the gateway, which names it again later. */
        public string $token,
        /** The account the card is kept at; it can only be charged there. */
        public ?string $paymentProviderToken,
        public string $holderName,
        /** The network the card belongs to, e.g. visa, as far as it is known. */
        public ?string $scheme,
        /** The head of the number: eight digits, or six for a number shorter than sixteen digits. */
        public string $firstDigits,
        public string $lastFourDigit,
        public string $expiryMonth,
        public string $expiryYear,
        /** Whether this is the card the customer pays with unless they say otherwise. */
        public bool $isDefault,
        public ?string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $card
     */
    public static function fromArray(array $card): self
    {
        return new self(
            token: (string) ($card['token'] ?? ''),
            paymentProviderToken: isset($card['payment_provider_token']) ? (string) $card['payment_provider_token'] : null,
            holderName: (string) ($card['holder_name'] ?? ''),
            scheme: isset($card['scheme']) ? (string) $card['scheme'] : null,
            firstDigits: (string) ($card['first_digits'] ?? ''),
            lastFourDigit: (string) ($card['last_four_digit'] ?? ''),
            expiryMonth: (string) ($card['expiry_month'] ?? ''),
            expiryYear: (string) ($card['expiry_year'] ?? ''),
            isDefault: (bool) ($card['is_default'] ?? false),
            createdAt: isset($card['created_at']) ? (string) $card['created_at'] : null,
        );
    }
}
