<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\AmountType;
use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\CurrencyType;
use Gurmehub\Odemehub\Enum\TaxMode;

/**
 * A change to a payment link, named by its token in the address and again
 * in the body. Only what is sent is written; lines sent replace every line
 * there was. A payment under way does not stand in the way: the payments
 * to come are charged as the link now is. A link turned back to `fixed`,
 * or left so, has to have lines; one whose last day has gone by is
 * switched on only by giving it a new day.
 */
final readonly class UpdatePaymentLink extends Message
{
    /**
     * @param  list<Item>|null  $items
     * @param  list<string>|null  $predefinedAmounts  The amounts the payer picks from; at most ten.
     * @param  list<Currency>|null  $currencies  The money the payer may pick from.
     * @param  list<string>  $clear  Fields to set to nothing: 'description', 'expires_at', 'payment_provider_token', 'item_name', 'predefined_amounts', 'tax_rate', 'currencies'.
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
        public ?AmountType $amountType = null,
        public ?string $itemName = null,
        public ?array $predefinedAmounts = null,
        /** The tax on the payer's amount, as a percentage. */
        public ?string $taxRate = null,
        public ?TaxMode $taxMode = null,
        public ?CurrencyType $currencyType = null,
        public ?array $currencies = null,
        /** Whether the payer is sent an e-mail, at the address they give on the checkout page, once their payment goes through. */
        public ?bool $emailsCustomer = null,
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
            'amount_type' => $this->amountType?->value,
            'item_name' => $this->itemName,
            'predefined_amounts' => $this->predefinedAmounts,
            'tax_rate' => $this->taxRate,
            'tax_mode' => $this->taxMode?->value,
            'currency' => $this->currency?->value,
            'currency_type' => $this->currencyType?->value,
            'currencies' => $this->currencies === null ? null : array_map(
                static fn (Currency $currency): string => $currency->value,
                $this->currencies,
            ),
            'emails_customer' => $this->emailsCustomer,
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
