<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * The banks whose accounts may be added for Banka Hareketleri, each with
 * the credentials it asks for. Nothing is sent but the signature.
 */
final readonly class RetrieveBanks extends Message
{
    public function path(): string
    {
        return 'retrieve-banks';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [];
    }
}
