<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * The cards kept for a customer. The card they pay with unless they say
 * otherwise comes first, the rest oldest first; a customer with none
 * answers an empty list.
 */
final readonly class SavedCardList
{
    use ReadsFields;

    /**
     * @param  list<SavedCard>  $savedCards
     */
    public function __construct(
        public Result $result,
        public array $savedCards,
        public SavedCardCustomer $customer,
    ) {}

    /**
     * The card the customer pays with unless they say otherwise.
     */
    public function default(): ?SavedCard
    {
        foreach ($this->savedCards as $savedCard) {
            if ($savedCard->isDefault) {
                return $savedCard;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        return new self(
            result: Result::fromArray($body),
            savedCards: self::each($body['saved_cards'] ?? null, SavedCard::fromArray(...)),
            customer: SavedCardCustomer::fromArray(self::object($body['customer'] ?? null) ?? []),
        );
    }
}
