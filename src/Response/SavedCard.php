<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\CardScheme;

/**
 * A card a customer let the merchant keep, as the gateway shows it: enough
 * to draw the card and to name it again, and nothing that could charge it.
 * What lets it be charged again stays with the gateway.
 */
final readonly class SavedCard
{
    use ReadsFields;

    public function __construct(
        /** The card's token in the gateway, which names it again later. */
        public string $token,
        /** The account the card is kept at; it can only be charged there. */
        public string $paymentProviderToken,
        public string $holderName,
        /** The network the card belongs to, e.g. visa, as far as it is known. */
        public ?CardScheme $scheme,
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
            token: self::text($card['token'] ?? null),
            paymentProviderToken: self::text($card['payment_provider_token'] ?? null),
            holderName: self::text($card['holder_name'] ?? null),
            scheme: self::oneOf(CardScheme::class, $card['scheme'] ?? null),
            firstDigits: self::text($card['first_digits'] ?? null),
            lastFourDigit: self::text($card['last_four_digit'] ?? null),
            expiryMonth: self::text($card['expiry_month'] ?? null),
            expiryYear: self::text($card['expiry_year'] ?? null),
            isDefault: (bool) ($card['is_default'] ?? false),
            createdAt: self::said($card['created_at'] ?? null),
        );
    }
}
