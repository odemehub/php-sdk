<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use Gurmehub\Odemehub\Enum\CardScheme;
use Gurmehub\Odemehub\Enum\CardType;

/**
 * What is known about a card from the head of its number. A gateway that
 * could not find out leaves the fields unsaid rather than guessing, so a
 * merchant asks the result whether the answer is worth reading before it
 * draws anything from it.
 */
final readonly class Bin
{
    use ReadsFields;

    /**
     * @param  list<Installment>  $installments  The ways the amount may be paid off, a single payment first. Empty for any money but the lira.
     */
    public function __construct(
        public Result $result,
        /** The digits the question was asked with. */
        public string $bin,
        /** The institution that issued the card. */
        public ?string $issuerName,
        public ?string $issuerCode,
        /** The network the card belongs to; null when it is not known. */
        public ?CardScheme $scheme,
        /** Whether the money is lent, drawn from an account or loaded beforehand; null when it is not known. */
        public ?CardType $type,
        /** The programme the card is sold under, such as Bonus or World. */
        public ?string $program,
        /** Whether the card belongs to a company rather than to a person. */
        public ?bool $isCommercial,
        public array $installments,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $card = self::object($body['card'] ?? null) ?? [];

        return new self(
            result: Result::fromArray($body),
            bin: self::text($card['bin'] ?? null),
            issuerName: self::said($card['issuer_name'] ?? null),
            issuerCode: self::said($card['issuer_code'] ?? null),
            scheme: self::oneOf(CardScheme::class, $card['scheme'] ?? null),
            type: self::oneOf(CardType::class, $card['type'] ?? null),
            program: self::said($card['program'] ?? null),
            isCommercial: self::flag($card['is_commercial'] ?? null),
            installments: self::each($body['installments'] ?? null, Installment::fromArray(...)),
        );
    }
}
