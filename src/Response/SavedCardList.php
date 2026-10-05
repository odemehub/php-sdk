<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * Kept cards asked after, each with the customer it is kept for. A
 * customer's cards come with the one they pay with by default first. The answer is always a list, oldest first, and an empty one when
 * nothing matched. The days answered are the ones the gateway used, when
 * the records were asked for by the days they were made on: the ones
 * asked for, or the last seven when none were.
 */
final readonly class SavedCardList
{
    use ReadsFields;

    /**
     * @param  list<SavedCard>  $savedCards
     */
    public function __construct(
        public Result $result,
        /** The first day listed, as `YYYY-MM-DD` in the team's timezone; null when they were asked for by token or reference. */
        public ?string $createdFrom,
        /** The last day listed, the same way. */
        public ?string $createdTo,
        public array $savedCards,
    ) {}

    /**
     * The card the customer pays with unless they say otherwise, when the
     * cards were asked for by the customer's reference.
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
            createdFrom: self::said($body['created_from'] ?? null),
            createdTo: self::said($body['created_to'] ?? null),
            savedCards: self::each($body['saved_cards'] ?? null, SavedCard::fromArray(...)),
        );
    }
}
