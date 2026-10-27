<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The most instalments the checkout page offers, apart for individual and
 * commercial cards: 1 for a single payment only, null for no limit on that
 * kind of card.
 */
final readonly class MaxInstallments
{
    use ReadsFields;

    public function __construct(
        /** The most instalments on an individual card; null for no limit. */
        public ?int $individual,
        /** The most instalments on a commercial card; null for no limit. */
        public ?int $commercial,
    ) {}

    /**
     * @param  array<string, mixed>  $maxInstallments
     */
    public static function fromArray(array $maxInstallments): self
    {
        return new self(
            individual: self::count($maxInstallments['individual'] ?? null),
            commercial: self::count($maxInstallments['commercial'] ?? null),
        );
    }
}
