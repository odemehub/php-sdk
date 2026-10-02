<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The answer to keeping a card, asking after one or making one the
 * default: the card as it now stands and whose it is. A card the provider
 * would not keep comes back as nothing, with the result saying why.
 */
final readonly class SavedCardDetails
{
    use ReadsFields;

    public function __construct(
        public Result $result,
        /** The card as it now stands, or null when the provider would not keep it. */
        public ?SavedCard $savedCard,
        public SavedCardCustomer $customer,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $savedCard = self::object($body['saved_card'] ?? null);

        return new self(
            result: Result::fromArray($body),
            savedCard: $savedCard === null ? null : SavedCard::fromArray($savedCard),
            customer: SavedCardCustomer::fromArray(self::object($body['customer'] ?? null) ?? []),
        );
    }
}
