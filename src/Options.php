<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub;

/**
 * The address the gateway is reached at and the credentials it is reached
 * with. A credential pair belongs to a single team, and the team is part of
 * the address, so a pair only ever opens its own team's endpoints.
 */
final readonly class Options
{
    /**
     * The header the API key travels in.
     */
    public const API_KEY_HEADER = 'X-Api-Key';

    public function __construct(
        /** The address the application is served from, e.g. https://app.odemehub.com. */
        public string $baseUrl,
        /** The team the payments are made on behalf of: the ten-digit workspace id the Entegrasyon page shows. */
        public string $team,
        public string $apiKey,
        public string $apiSecret,
    ) {}

    /**
     * The path of a gateway endpoint for this team, as it is signed: with
     * its leading slash and nothing in front of it.
     */
    public function path(string $endpoint): string
    {
        return '/api/'.$this->team.'/gateway/'.$endpoint;
    }

    /**
     * The full address of a gateway endpoint for this team.
     */
    public function url(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/').$this->path($endpoint);
    }
}
