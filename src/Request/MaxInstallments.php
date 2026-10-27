<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The most instalments the checkout page offers, apart for individual and
 * commercial cards: 1 to 12 each, 1 for a single payment only, null for no
 * limit on that kind of card. Sent on a change it takes the place of the
 * limit there was; `new MaxInstallments()` — both null — lifts it.
 */
final readonly class MaxInstallments
{
    public function __construct(
        /** The most instalments on an individual card; null for no limit. */
        public ?int $individual = null,
        /** The most instalments on a commercial card; null for no limit. */
        public ?int $commercial = null,
    ) {}

    /**
     * Both kinds of card, a null one included: a limit is sent whole.
     *
     * @return array{individual: int|null, commercial: int|null}
     */
    public function toArray(): array
    {
        return [
            'individual' => $this->individual,
            'commercial' => $this->commercial,
        ];
    }
}
