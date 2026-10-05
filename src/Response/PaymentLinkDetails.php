<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to opening or changing a payment link: the link as it now
 * stands. Its payments are on the link when it is asked after with
 * `retrievePaymentLinks`.
 */
final readonly class PaymentLinkDetails
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        public PaymentLink $paymentLink,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            paymentLink: PaymentLink::fromArray(self::object($body['payment_link'] ?? null) ?? []),
        );
    }
}
