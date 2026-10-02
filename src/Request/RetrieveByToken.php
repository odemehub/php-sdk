<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * One record asked after by the token the gateway gave it, as
 * `GET retrieve-{resource}/{token}`. There is no body: the signature is
 * taken over the empty string, and the token travels in the address. A
 * caller only ever reaches its own team's records; anybody else's is
 * turned down as though it did not exist, with `NotFoundException`.
 */
abstract readonly class RetrieveByToken extends Message
{
    public function __construct(
        /** The record's token in the gateway, as it was answered when the record was made. */
        public string $token,
    ) {}

    /**
     * The endpoint, without the token.
     */
    abstract protected function endpoint(): string;

    public function method(): string
    {
        return 'GET';
    }

    public function path(): string
    {
        return $this->endpoint().'/'.$this->token;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $channelToken): array
    {
        return [];
    }
}
