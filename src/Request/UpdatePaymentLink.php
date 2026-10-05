<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A change to a payment link, named by its token in the address and again
 * in the body. Only what is sent is written; lines sent replace every line
 * there was. Nothing changes while a payment on it is under way.
 */
final readonly class UpdatePaymentLink extends Message
{
    /**
     * @param  list<Item>|null  $items
     * @param  list<string>  $clear  Fields to set to nothing: 'description', 'expires_at', 'payment_provider_token'.
     */
    public function __construct(
        /** The link's token in the gateway. */
        public string $token,
        public ?array $items = null,
        public ?Currency $currency = null,
        public ?string $reference = null,
        public ?string $description = null,
        public ?string $paymentProviderToken = null,
        public ?string $expiresAt = null,
        public ?bool $isActive = null,
        public array $clear = [],
    ) {}

    public function path(): string
    {
        return 'update-payment-link/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $link = self::said([
            'reference' => $this->reference,
            'description' => $this->description,
            'payment_provider_token' => $this->paymentProviderToken,
            'currency' => $this->currency?->value,
            'expires_at' => $this->expiresAt,
            'is_active' => $this->isActive,
            'items' => $this->items === null ? null : array_map(
                static fn (Item $item): array => $item->toArray(),
                $this->items,
            ),
        ]);

        foreach ($this->clear as $field) {
            $link[$field] = null;
        }

        return [
            'token' => $this->token,
            'payment_link' => $link,
        ];
    }
}
