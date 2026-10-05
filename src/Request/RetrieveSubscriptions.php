<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Subscriptions asked after, each with its customer and the renewal it is
 * on.
 */
final readonly class RetrieveSubscriptions extends Retrieve
{
    public function path(): string
    {
        return 'retrieve-subscriptions';
    }
}
