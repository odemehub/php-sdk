<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A payment link: an address that takes a payment again and again, from
 * whoever has it, until it is switched off or runs out. Nobody is named on
 * it: every payer says who they are on the checkout page.
 */
final readonly class CreatePaymentLink extends Message
{
    /**
     * @param  list<Item>  $items
     */
    public function __construct(
        public array $items,
        public Currency $currency,
        /** The reference it is known by in the calling system; left out, the gateway gives the team's next LINK number. Has to carry a digit. */
        public ?string $reference = null,
        public ?string $description = null,
        /** The payment account it is paid through; left out, the team's rules and its default account decide. */
        public ?string $paymentProviderToken = null,
        /** The last day it takes payments, as `YYYY-MM-DD` in the team's own calendar; never a day already gone by. */
        public ?string $expiresAt = null,
        /** Left out, the link is switched on. */
        public ?bool $isActive = null,
    ) {}

    public function path(): string
    {
        return 'create-payment-link';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'payment_link' => self::said([
                'reference' => $this->reference,
                'description' => $this->description,
                'payment_provider_token' => $this->paymentProviderToken,
                'currency' => $this->currency->value,
                'expires_at' => $this->expiresAt,
                'is_active' => $this->isActive,
                'items' => array_map(
                    static fn (Item $item): array => $item->toArray(),
                    $this->items,
                ),
            ]),
        ];
    }
}
