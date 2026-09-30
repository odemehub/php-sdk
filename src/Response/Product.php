<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A product as the gateway now keeps it, named by the merchant's own key
 * for it on its channel.
 */
final readonly class Product
{
    public function __construct(
        public Result $result,
        /** The channel the product is sold on. */
        public string $channelToken,
        /** The key the product is known by in the calling system. */
        public string $channelReference,
        public string $name,
        /** simple or recurring. */
        public string $type,
        /** The price of one, as digits with the kurus behind a point. */
        public string $amount,
        public string $currency,
        /** The tax included in the price, as a percentage. */
        public string $taxRate,
        /** monthly or annually for a recurring product; nothing for a simple one. */
        public ?string $period,
        /** Whether it is on sale. */
        public bool $isActive,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $product = is_array($body['product'] ?? null) ? $body['product'] : [];

        return new self(
            result: Result::fromArray($body),
            channelToken: (string) ($product['channel_token'] ?? ''),
            channelReference: (string) ($product['channel_reference'] ?? ''),
            name: (string) ($product['name'] ?? ''),
            type: (string) ($product['type'] ?? ''),
            amount: (string) ($product['amount'] ?? ''),
            currency: (string) ($product['currency'] ?? ''),
            taxRate: (string) ($product['tax_rate'] ?? ''),
            period: isset($product['period']) ? (string) $product['period'] : null,
            isActive: (bool) ($product['is_active'] ?? false),
        );
    }
}
