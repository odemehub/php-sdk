<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * A bank whose accounts may be added for Banka Hareketleri, with the
 * credentials it asks for.
 */
final readonly class Bank
{
    use ReadsFields;

    /**
     * @param  list<BankField>  $fields  The credentials the bank asks for, each sent in `settings` under its key.
     */
    public function __construct(
        /** The bank's id, sent as `provider` when an account is added, e.g. `enpara`. */
        public string $id,
        /** The bank's name, e.g. `Enpara`. */
        public string $title,
        public array $fields,
    ) {}

    /**
     * @param  array<string, mixed>  $bank
     */
    public static function fromArray(array $bank): self
    {
        return new self(
            id: self::text($bank['id'] ?? null),
            title: self::text($bank['title'] ?? null),
            fields: self::each($bank['fields'] ?? null, BankField::fromArray(...)),
        );
    }
}
