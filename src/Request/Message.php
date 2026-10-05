<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Something handed to the gateway. Everything sent there is plain JSON,
 * posted and signed as a whole by the client, so what is common to all of
 * them is the endpoint it goes to and the body it is sent as.
 */
abstract readonly class Message
{
    /**
     * The endpoint this is sent to, under the team's gateway, with the
     * token in the address where the endpoint takes one.
     */
    abstract public function path(): string;

    /**
     * The request body, in the snake_case the gateway speaks.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * The HTTP method this goes with: every endpoint is posted to.
     */
    public function method(): string
    {
        return 'POST';
    }

    /**
     * Drop what the caller left unsaid, so an optional field is left out of
     * the body altogether rather than sent empty.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected static function said(array $body): array
    {
        return array_filter($body, static fn (mixed $value): bool => $value !== null);
    }
}
