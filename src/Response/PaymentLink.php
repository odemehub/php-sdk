<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\AmountType;
use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Enum\CurrencyType;
use Gurmehub\Odemehub\Enum\TaxMode;

/**
 * A payment link as the gateway keeps it: what it is for — its lines, or
 * the amount the payer picks and the tax on it — what it comes to, in which
 * money, whether it takes payments and until when, the environment its
 * payments are taken in now, and the address that is the link itself —
 * only while it can be paid. A link has no customer and no shipping of its
 * own; each payment made on it carries those.
 */
final readonly class PaymentLink
{
    use ReadsFields;

    /**
     * @param  list<Item>  $items  What the link is for; empty on a link whose amount the payer picks.
     * @param  list<string>|null  $predefinedAmounts  The amounts the payer picks from; null but for `predefined` and `predefined_and_custom`.
     * @param  list<Currency>|null  $currencies  The money the payer picks from, the link's own currency among them; null on a link of one money.
     */
    public function __construct(
        /** The link's token in the gateway; name it here to ask after or change it later. */
        public string $token,
        /** The reference the link is known by: the merchant's, or `LINK{n}` when the gateway made it up. */
        public string $reference,
        public ?string $description,
        /** The account the link is paid through; null when none was named. */
        public ?string $paymentProviderToken,
        public array $items,
        /** What the lines come to before tax; null on a link whose amount the payer picks. */
        public ?string $subtotal,
        /** The tax on the lines; null on a link whose amount the payer picks. */
        public ?string $taxAmount,
        /** What the link comes to in all, which is what each payment charges; null on a link whose amount the payer picks. */
        public ?string $amount,
        /** The money it is paid in; where the payer picks, the one picked to begin with. */
        public ?Currency $currency,
        /** Whether the link takes payments right now: switched on and not past its day. */
        public bool $isActive,
        /** The last moment it may be paid — the end of the day it was given, in the team's timezone — as an ISO 8601 time in UTC; null for one that never runs out. */
        public ?string $expiresAt,
        /** The link itself: where the customer pays; null while it cannot be paid (switched off, past its day, or the team's payment links are off). */
        public ?string $checkoutUrl,
        public ?string $createdAt,
        /** Whether its payments are taken in the test environment now: the team's, since a link has no environment of its own. */
        public bool $isTest = false,
        /** The latest attempts made on the link, at most fifty, newest first, the refused ones included; listed links only. */
        public array $transactions = [],
        /** How many attempts have been made on the link in all, however many are listed; null but on a listed link. */
        public ?int $transactionsCount = null,
        /** What the payer pays: the lines (`fixed`) or an amount they pick; null for a type this client does not know. */
        public ?AmountType $amountType = null,
        /** The name the payer's amount is paid under; null on a link of lines. */
        public ?string $itemName = null,
        public ?array $predefinedAmounts = null,
        /** The tax on the payer's amount, as a percentage; null when it carries none. */
        public ?string $taxRate = null,
        /** How the tax rate is read against the payer's amount; null for a mode this client does not know. */
        public ?TaxMode $taxMode = null,
        /** Whether the payer picks the money; null for a type this client does not know. */
        public ?CurrencyType $currencyType = null,
        public ?array $currencies = null,
        /** Whether the payer is sent an e-mail once their payment goes through. */
        public bool $emailsPayer = false,
    ) {}

    /**
     * The listed attempts that went through.
     *
     * @return list<Transaction>
     */
    public function successful(): array
    {
        return array_values(array_filter(
            $this->transactions,
            static fn (Transaction $transaction): bool => $transaction->isSuccessful(),
        ));
    }

    /**
     * Whether the payer says what they pay, rather than paying the lines.
     */
    public function isChosenByPayer(): bool
    {
        return $this->amountType !== null && $this->amountType !== AmountType::Fixed;
    }

    /**
     * @param  array<string, mixed>  $link
     */
    public static function fromArray(array $link): self
    {
        return new self(
            token: self::text($link['token'] ?? null),
            reference: self::text($link['reference'] ?? null),
            description: self::said($link['description'] ?? null),
            paymentProviderToken: self::said($link['payment_provider_token'] ?? null),
            items: self::each($link['items'] ?? null, Item::fromArray(...)),
            subtotal: self::said($link['subtotal'] ?? null),
            taxAmount: self::said($link['tax_amount'] ?? null),
            amount: self::said($link['amount'] ?? null),
            currency: self::oneOf(Currency::class, $link['currency'] ?? null),
            isActive: (bool) ($link['is_active'] ?? false),
            expiresAt: self::said($link['expires_at'] ?? null),
            checkoutUrl: self::said($link['checkout_url'] ?? null),
            createdAt: self::said($link['created_at'] ?? null),
            isTest: (bool) ($link['is_test'] ?? false),
            transactions: self::each($link['transactions'] ?? null, Transaction::fromArray(...)),
            transactionsCount: self::count($link['transactions_count'] ?? null),
            amountType: self::oneOf(AmountType::class, $link['amount_type'] ?? null),
            itemName: self::said($link['item_name'] ?? null),
            predefinedAmounts: is_array($link['predefined_amounts'] ?? null)
                ? array_values(array_map(self::text(...), $link['predefined_amounts']))
                : null,
            taxRate: self::said($link['tax_rate'] ?? null),
            taxMode: self::oneOf(TaxMode::class, $link['tax_mode'] ?? null),
            currencyType: self::oneOf(CurrencyType::class, $link['currency_type'] ?? null),
            currencies: is_array($link['currencies'] ?? null)
                ? array_values(array_filter(array_map(
                    static fn (mixed $currency): ?Currency => self::oneOf(Currency::class, $currency),
                    $link['currencies'],
                )))
                : null,
            emailsPayer: (bool) ($link['emails_payer'] ?? false),
        );
    }
}
