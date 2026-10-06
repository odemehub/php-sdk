<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A payment link named, and nothing more: its token in the gateway and the
 * reference it is known by. It is how a payment at a link points at the
 * link it was paid at; ask after the link itself with
 * `retrievePaymentLinks`.
 */
final readonly class PaymentLinkReference
{
    use ReadsFields;

    public function __construct(
        /** The link's token in the gateway. */
        public string $token,
        /** The reference the link is known by: the merchant's, or `LINK{n}` when the gateway made it up. */
        public string $reference,
    ) {}

    /**
     * @param  array<string, mixed>  $link
     */
    public static function fromArray(array $link): self
    {
        return new self(
            token: self::text($link['token'] ?? null),
            reference: self::text($link['reference'] ?? null),
        );
    }
}
