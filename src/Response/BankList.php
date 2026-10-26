<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The banks whose accounts may be added, each with the credentials it asks
 * for.
 */
final readonly class BankList
{
    use ReadsFields;

    /**
     * @param  list<Bank>  $banks
     */
    public function __construct(
        public Result $result,
        public array $banks,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            banks: self::each($body['banks'] ?? null, Bank::fromArray(...)),
        );
    }
}
