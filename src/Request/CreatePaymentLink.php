<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

use Gurmehub\Odemehub\Enum\Currency;

/**
 * A payment link: a page on the gateway that is paid again and again, by
 * anybody who has the address, until it is switched off or its day runs
 * out. There is no customer; whoever pays says who they are on the page.
 * The answer carries `checkout_url`, which is the link itself, while it
 * can be paid.
 *
 * Opening is idempotent per channel and reference: opening again under a
 * reference that already has a link overwrites that link with what is
 * sent and answers with it, under its own token — a link is never used
 * up. Only a link with a payment under way is left alone. A link opened
 * without a reference is given one of the form `LINK{n}`.
 *
 * Give `ChannelMessage::ODEMEHUB_CHANNEL` as the channel to open the link
 * on the team's own ödemehub channel, where the panel opens its links.
 */
final readonly class CreatePaymentLink extends ChannelMessage
{
    /**
     * @param  list<Item>  $items  What the link is for; at least one line.
     */
    public function __construct(
        public array $items,
        public Currency $currency,
        /** The reference the link is known by in the calling system. Has to carry at least one digit. Left out, the gateway makes one up. */
        public ?string $channelReference = null,
        public ?string $description = null,
        /** The account the link is paid through; it has to take 3D payments. Left out, Gate rules and the default account decide at pay time. */
        public ?string $paymentProviderToken = null,
        /** The last day the link may be paid, as `YYYY-MM-DD` in the team's timezone; today or later. Left out, it never runs out. */
        public ?string $expiresAt = null,
        /** Whether the link takes payments. Left out, it does. */
        public ?bool $isActive = null,
        ?string $channelToken = null,
    ) {
        parent::__construct($channelToken);
    }

    public function path(): string
    {
        return 'create-payment-link';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [
            'payment_link' => self::said([
                'channel_token' => $this->linkChannel($channelToken),
                'channel_reference' => $this->channelReference,
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
