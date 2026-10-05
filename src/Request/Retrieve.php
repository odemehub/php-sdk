<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Request;

/**
 * Asking after records of one kind. They are named one of three ways: by
 * the token the gateway gave one, by the merchant's own reference for them,
 * or by the days they were made on, as `YYYY-MM-DD` in the team's own
 * timezone, both ends included and at most seven days apart. Asked without
 * any of these, it is the last seven days up to today. The answer is
 * always a list, oldest first, and an empty one when nothing matches.
 * Nothing is changed by asking.
 */
abstract readonly class Retrieve extends Message
{
    final public function __construct(
        /** The record's token in the gateway. */
        public ?string $token = null,
        /** The merchant's own reference for them. */
        public ?string $reference = null,
        /** The first day, as `YYYY-MM-DD`. Given together with `createdTo`. */
        public ?string $createdFrom = null,
        /** The last day, as `YYYY-MM-DD`, at most six days after the first. */
        public ?string $createdTo = null,
    ) {}

    /**
     * The one record with the token.
     */
    public static function byToken(string $token): static
    {
        return new static(token: $token);
    }

    /**
     * The records under the merchant's own reference.
     */
    public static function byReference(string $reference): static
    {
        return new static(reference: $reference);
    }

    /**
     * The records made between two days, both included.
     */
    public static function between(string $createdFrom, string $createdTo): static
    {
        return new static(createdFrom: $createdFrom, createdTo: $createdTo);
    }

    /**
     * The records made in the last seven days.
     */
    public static function latest(): static
    {
        return new static;
    }

    /**
     * The field the merchant's own reference travels in.
     */
    protected function referenceField(): string
    {
        return 'reference';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::said([
            'token' => $this->token,
            $this->referenceField() => $this->reference,
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
        ]);
    }
}
