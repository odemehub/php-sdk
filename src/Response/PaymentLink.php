<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A payment link as the gateway keeps it: what it is for, what it comes
 * to, whether it takes payments and until when, the environment its
 * payments are taken in now, and the address that is the link itself —
 * only while it can be paid. A link has no customer and no shipping of its
 * own; each payment made on it carries those.
 */
final readonly class PaymentLink
{
    use ReadsFields;

    /**
     * @param  list<Item>  $items  What the link is for.
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
        /** What the lines come to before tax. */
        public string $subtotal,
        /** The tax on the lines. */
        public string $taxAmount,
        /** What the link comes to in all, which is what each payment charges. */
        public string $amount,
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
            subtotal: self::text($link['subtotal'] ?? null),
            taxAmount: self::text($link['tax_amount'] ?? null),
            amount: self::text($link['amount'] ?? null),
            currency: self::oneOf(Currency::class, $link['currency'] ?? null),
            isActive: (bool) ($link['is_active'] ?? false),
            expiresAt: self::said($link['expires_at'] ?? null),
            checkoutUrl: self::said($link['checkout_url'] ?? null),
            createdAt: self::said($link['created_at'] ?? null),
            isTest: (bool) ($link['is_test'] ?? false),
            transactions: self::each($link['transactions'] ?? null, Transaction::fromArray(...)),
            transactionsCount: self::count($link['transactions_count'] ?? null),
        );
    }
}
