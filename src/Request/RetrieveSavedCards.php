<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Kept cards asked after: one by its token, every card of a customer by the
 * merchant's reference for them, or the ones kept between two days. A
 * customer's cards come with the one they pay with by default first.
 */
final readonly class RetrieveSavedCards extends Retrieve
{
    public function path(): string
    {
        return 'retrieve-saved-cards';
    }

    /**
     * A card is named by the reference of the customer it is kept for.
     */
    protected function referenceField(): string
    {
        return 'customer_reference';
    }
}
