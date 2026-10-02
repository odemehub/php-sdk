<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

use BackedEnum;

/**
 * How an answer's fields are read out of its JSON. The gateway's shapes
 * are settled, but an answer is still read defensively: a field that is
 * not there reads as empty rather than raising, so one unexpected key
 * never hides the outcome of a payment.
 */
trait ReadsFields
{
    /**
     * A field that is always there, as text.
     */
    protected static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * A field the gateway may have left empty, which then reads as nothing
     * rather than as an empty string, so there is one way of asking
     * whether it was said.
     */
    protected static function said(mixed $value): ?string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    /**
     * A yes-or-no the gateway may have left unsaid.
     */
    protected static function flag(mixed $value): ?bool
    {
        return $value === null ? null : (bool) $value;
    }

    /**
     * A whole number the gateway may have left unsaid.
     */
    protected static function count(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }

    /**
     * One of a fixed set of values, as its enum. A value that was not said,
     * or one this client does not know yet, reads as nothing, so a value
     * the gateway adds later never stops an answer from being read.
     *
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T|null
     */
    protected static function oneOf(string $enum, mixed $value): ?BackedEnum
    {
        return is_string($value) || is_int($value) ? $enum::tryFrom($value) : null;
    }

    /**
     * An object, or nothing when it was not there or was null.
     *
     * @return array<string, mixed>|null
     */
    protected static function object(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }

    /**
     * A list of objects, each read by the given reader; an empty list when
     * there was none.
     *
     * @template T
     *
     * @param  callable(array<string, mixed>): T  $reader
     * @return list<T>
     */
    protected static function each(mixed $value, callable $reader): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(
            static fn (mixed $item) => $reader(is_array($item) ? $item : []),
            $value,
        ));
    }
}
