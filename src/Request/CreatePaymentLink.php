<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\AmountType;
use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\CurrencyType;
use Gurmehub\Odemehub\Enum\TaxMode;

/**
 * A payment link: an address that takes a payment again and again, from
 * whoever has it, until it is switched off or runs out. Nobody is named on
 * it: every payer says who they are on the checkout page.
 *
 * A link is paid as its lines add up, or — with an amount type other than
 * `fixed` — at an amount the payer picks, paid as one line under
 * `itemName` with `taxRate` on it; any lines sent are then passed over. It
 * is paid in its one money, or in one of `currencies` the payer picks when
 * the currency type is `selectable`.
 *
 * Every call opens a new link under a token of its own, even under a
 * reference sent before; keep the token that comes back to change the link
 * or ask after it later.
 */
final readonly class CreatePaymentLink extends Message
{
    /**
     * @param  list<Item>|null  $items  What the link is for: at least one line on a link of lines (`fixed`, the default); passed over for the other amount types.
     * @param  list<string>|null  $predefinedAmounts  The amounts the payer picks from, for `predefined` and `predefined_and_custom`; at most ten.
     * @param  list<Currency>|null  $currencies  The money the payer may pick from, for `selectable`; the link's own currency is always among them.
     */
    public function __construct(
        /** The money the link is paid in; where the payer picks, the one picked to begin with. */
        public Currency $currency,
        public ?array $items = null,
        /** The reference it is known by in the calling system; left out, the gateway gives the team's next LINK number. Has to carry a digit. */
        public ?string $reference = null,
        public ?string $description = null,
        /** The payment account it is paid through; left out, the team's rules and its default account decide. */
        public ?string $paymentProviderToken = null,
        /** The last day it takes payments, as `YYYY-MM-DD` in the team's own calendar; never a day already gone by. */
        public ?string $expiresAt = null,
        /** Left out, the link is switched on. */
        public ?bool $isActive = null,
        /** What the payer pays; left out, the lines (`fixed`). */
        public ?AmountType $amountType = null,
        /** The name the payer's amount is paid under; needed for every amount type but `fixed`. */
        public ?string $itemName = null,
        public ?array $predefinedAmounts = null,
        /** The tax on the payer's amount, as a percentage: '20' or '20.00'. Left out, it carries none. */
        public ?string $taxRate = null,
        /** How the tax rate is read against the payer's amount; left out, `inclusive`. */
        public ?TaxMode $taxMode = null,
        /** Whether the payer picks the money; left out, `fixed`. */
        public ?CurrencyType $currencyType = null,
        public ?array $currencies = null,
        /** Whether the payer is sent an e-mail once their payment goes through; left out, they are not. */
        public ?bool $emailsPayer = null,
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
                'amount_type' => $this->amountType?->value,
                'item_name' => $this->itemName,
                'predefined_amounts' => $this->predefinedAmounts,
                'tax_rate' => $this->taxRate,
                'tax_mode' => $this->taxMode?->value,
                'currency' => $this->currency->value,
                'currency_type' => $this->currencyType?->value,
                'currencies' => $this->currencies === null ? null : array_map(
                    static fn (Currency $currency): string => $currency->value,
                    $this->currencies,
                ),
                'emails_payer' => $this->emailsPayer,
                'expires_at' => $this->expiresAt,
                'is_active' => $this->isActive,
                'items' => $this->items === null ? null : array_map(
                    static fn (Item $item): array => $item->toArray(),
                    $this->items,
                ),
            ]),
        ];
    }
}
