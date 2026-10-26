<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * One credential a bank asks for before its accounts can be read: the key
 * it is sent under in `settings`, and what the panel calls it.
 */
final readonly class BankField
{
    use ReadsFields;

    public function __construct(
        /** The key the credential is sent under in `settings`, e.g. `client_id`. */
        public string $key,
        /** Whether an account cannot be added without it. */
        public bool $required,
        /** What the panel calls the credential, e.g. `Client ID`. */
        public string $label,
    ) {}

    /**
     * @param  array<string, mixed>  $field
     */
    public static function fromArray(array $field): self
    {
        return new self(
            key: self::text($field['key'] ?? null),
            required: (bool) ($field['required'] ?? false),
            label: self::text($field['label'] ?? null),
        );
    }
}
