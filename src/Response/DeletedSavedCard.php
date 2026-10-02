<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to letting go of a kept card. There is nothing left of the
 * card to show, so it comes back carrying only the token it had, with
 * whose it was. A card the provider would not let go of stays, and the
 * result says why.
 */
final readonly class DeletedSavedCard
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        /** The token the card had. */
        public string $savedCardToken,
        public SavedCardCustomer $customer,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $savedCard = self::object($body['saved_card'] ?? null) ?? [];

        return new self(
            result: Result::fromArray($body),
            savedCardToken: self::text($savedCard['token'] ?? null),
            customer: SavedCardCustomer::fromArray(self::object($body['customer'] ?? null) ?? []),
        );
    }
}
