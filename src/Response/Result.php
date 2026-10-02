<?php

declare(strict_types=1);

namespace Gurmehub\Odemehub\Response;

/**
 * How a request went, as every answer opens: whether it worked and, only
 * when it did not, what went wrong, in Turkish. Something that worked has
 * nothing to say beyond that it did. A payment still under way says
 * nothing either: it has not failed yet.
 */
final readonly class Result
{
    use ReadsFields;

    public function __construct(
        public bool $successful,
        public ?string $message,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromArray(array $body): self
    {
        $result = self::object($body['result'] ?? null) ?? [];

        return new self(
            successful: (bool) ($result['successful'] ?? false),
            message: self::said($result['message'] ?? null),
        );
    }
}
