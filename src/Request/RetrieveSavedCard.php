<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One kept card, by its token, with the reference and e-mail of the
 * customer it is kept for.
 */
final readonly class RetrieveSavedCard extends RetrieveByToken
{
    protected function endpoint(): string
    {
        return 'retrieve-saved-card';
    }
}
