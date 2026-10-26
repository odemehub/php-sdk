<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Bank accounts asked after: the ones whose tokens are given, or every
 * account of the team's when none is. A token that is not the team's
 * finds nothing. Nothing is changed by asking.
 */
final readonly class RetrieveBankAccounts extends Message
{
    /**
     * @param  list<string>  $tokens  The accounts' tokens; empty for every account.
     */
    public function __construct(
        public array $tokens = [],
    ) {}

    public function path(): string
    {
        return 'retrieve-bank-accounts';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->tokens === [] ? [] : ['tokens' => array_values($this->tokens)];
    }
}
